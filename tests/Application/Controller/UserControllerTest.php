<?php

namespace App\Tests\Application\Controller;

use App\Entity\Role;
use App\Type\Gender;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;

class UserControllerTest extends AbstractApplicationTestCase
{
    protected KernelBrowser $client;

    protected RouterInterface $router;

    public static function InvalidCredentialsDataProvider(): array
    {
        return [
            'empty' => ['', ''],
            'invalid username' => ['user_not_exist', 'test'],
            'invalid password' => ['user', 'invalid password'],
            'empty password' => ['user', ''],
            'empty user' => ['', 'user'],
            'sql injection' => ['user', '1\' OR \'1\' =\'1'],
        ];
    }

    public static function invalidUserDataProvider(): array
    {
        return [
            'blank email' => [
                ['edit_user[email]' => ''],
                'This value should not be blank.',
            ],
            'invalid email' => [
                ['edit_user[email]' => 'invalid email address'],
                'This value is not a valid email address.',
            ],
            'very long email' => [
                ['edit_user[email]' => str_repeat('a', 59) . '@a.com'],
                'This value is too long. It should have 64 characters or less.',
            ],
            'blank first name' => [
                ['edit_user[firstName]' => ''],
                'This value should not be blank.',
            ],
            'first name too long' => [
                ['edit_user[firstName]' => str_repeat('a', 33)],
                'This value is too long. It should have 32 characters or less.',
            ],
            'blank last name' => [
                ['edit_user[lastName]' => ''],
                'This value should not be blank.',
            ],
            'last name too long' => [
                ['edit_user[lastName]' => str_repeat('a', 33)],
                'This value is too long. It should have 32 characters or less.',
            ],
            'invalid birth date' => [
                ['edit_user[birthday]' => '27.13.1659'],
                'Please enter a valid birthdate.',
            ],
        ];
    }

    public static function passwordChangeFailureDataProvider(): array
    {
        return [
            'empty current password' => [
                ['user_passwords[currentPassword]' => null],
                'This value should not be blank.',
            ],
            'too big current password' => [
                ['user_passwords[currentPassword]' => str_repeat('a', 33)],
                'This value is too long. It should have 32 characters or less.',
            ],
            'incorrect current password' => [
                ['user_passwords[currentPassword]' => 'this is not a password'],
                'Current password is incorrect.'
            ],
            'empty new password' => [
                [
                    'user_passwords[newPassword][first]' => null,
                    'user_passwords[newPassword][second]' => null,
                ],
                'This value should not be blank.',
            ],
            'too long new password' => [
                [
                    'user_passwords[newPassword][first]' => str_repeat('a', 33),
                    'user_passwords[newPassword][second]' => str_repeat('a', 33),
                ],
                'This value is too long. It should have 32 characters or less.',
            ],
            'passwords do not match' => [
                [
                    'user_passwords[newPassword][first]' => str_repeat('a', 32),
                    'user_passwords[newPassword][second]' => str_repeat('b', 32),
                ],
                'The password fields must match.',
            ],
        ];
    }

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->router = $this->getContainer()->get(RouterInterface::class);
    }

    public function testLoginSuccess(): void
    {
        $this->client->request('GET', $this->router->generate('user-login'));
        $this->client->submitForm('user-login', [
            '_username' => 'user',
            '_password' => 'user',
        ]);
        $this->assertResponseRedirects($this->router->generate('home'));
        $this->client->followRedirect();
        $this->assertSelectorExists('#user-profile');

    }

    /**
     * @dataProvider InvalidCredentialsDataProvider
     *
     */
    public function testLoginFailed($username, $password): void
    {
        $this->client->request('GET', $this->router->generate('user-login'));
        $this->client->submitForm('user-login', [
            '_username' => $username,
            '_password' => $password,
        ]);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Invalid credentials.');
    }

    public function testEditUserData(): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('user-edit-general'));

        $this->client->submitForm('Save', [
            'edit_user[email]' => 'test@email.com',
            'edit_user[firstName]' => 'New firstname',
            'edit_user[lastName]' => 'New lastname',
            'edit_user[birthday]' => '01.01.1961',
            'edit_user[sex]' => Gender::FEMALE
        ]);

        $this->assertSelectorTextContains('.toast-body', 'User data updated.');
        $this->assertInputValueSame('edit_user[email]', 'test@email.com');
        $this->assertInputValueSame('edit_user[firstName]', 'New firstname');
        $this->assertInputValueSame('edit_user[lastName]', 'New lastname');
        $this->assertInputValueSame('edit_user[birthday]', '01.01.1961');
        $this->assertFormValue('form[name="edit_user"]', 'edit_user[sex]', Gender::FEMALE);
    }

    /**
     * @dataProvider invalidUserDataProvider
     */
    public function testEditUserDataError(array $invalidData, string $errorMessage): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('user-edit-general'));

        $validData = [
            'edit_user[email]' => 'test@email.com',
            'edit_user[firstName]' => 'New firstname',
            'edit_user[lastName]' => 'New lastname',
            'edit_user[birthday]' => '01.01.1961',
            'edit_user[sex]' => Gender::FEMALE
        ];

        $data = array_merge($validData, $invalidData);

        $this->client->submitForm('Save', $data);
        $this->assertSelectorTextContains('.form_validation_error', $errorMessage);
    }

    public function testIncorrectGender(): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('user-edit-general'));

        $select = $this->client->getCrawler()->filter('#edit_user_sex')->getNode(0);
        $invalidOption = $select->ownerDocument->createElement('option', 'invalid gender');
        $invalidOption->setAttribute('value', '3');
        $select->appendChild($invalidOption);

        $this->client->submitForm('Save', [
            'edit_user[sex]' => 3,
        ]);

        $this->assertSelectorTextContains('.form_validation_error', 'The selected choice is invalid.');
    }

    public function testNotAllowedToUpdateOtherUser(): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('user-edit-general', ['id' => 1]));
        $this->assertResponseStatusCodeSame(404);
    }

    public function testAdminCanUpdateOtherUsers(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('user-edit-general', ['id' => 2]));
        $this->client->submitForm('Save', [
            'edit_user[email]' => 'test@email.com',
            'edit_user[firstName]' => 'New firstname',
            'edit_user[lastName]' => 'New lastname',
            'edit_user[birthday]' => '01.01.1961',
            'edit_user[sex]' => Gender::FEMALE
        ]);

        $this->assertSelectorTextContains('.toast-body', 'User data updated.');
        $this->assertInputValueSame('edit_user[email]', 'test@email.com');
        $this->assertInputValueSame('edit_user[firstName]', 'New firstname');
        $this->assertInputValueSame('edit_user[lastName]', 'New lastname');
        $this->assertInputValueSame('edit_user[birthday]', '01.01.1961');
        $this->assertFormValue('form[name="edit_user"]', 'edit_user[sex]', Gender::FEMALE);
    }

    public function testPasswordChangesSuccess(): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('user-edit-password'));

        $this->client->submitForm('Save', [
            'user_passwords[currentPassword]' => 'user',
            'user_passwords[newPassword][first]' => 'new password',
            'user_passwords[newPassword][second]' => 'new password',
        ]);

        $this->assertSelectorTextContains('.toast-body', 'User password changed.');

        $user = $this->getUser('user');

        $hasher = $this->getContainer()->get(UserPasswordHasherInterface::class);

        $this->assertTrue($hasher->isPasswordValid($user, 'new password'));
    }

    /**
     * @dataProvider passwordChangeFailureDataProvider
     */
    public function testPasswordChangeFailure(array $invalidData, string $errorMessage): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('user-edit-password'));

        $validData = [
            'user_passwords[currentPassword]' => 'user',
            'user_passwords[newPassword][first]' => 'new password',
            'user_passwords[newPassword][second]' => 'new password',
        ];

        $data = array_merge($validData, $invalidData);

        $this->client->submitForm('Save', $data);
        $this->assertSelectorTextContains('.form_validation_error', $errorMessage);
    }
    
    public function testRoleChange(): void
    {
        $user = $this->getUser('user');
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('user-edit-roles', ['id' => $user->getId()]));

        $form = $this->client->getCrawler()->selectButton('Set roles')->form();
        
        foreach ($form->get('roles') as $checkbox) {
            $checkbox->tick();
        }

        $this->client->submit($form);

        $user = $this->getUser('user');
        
        $this->assertSelectorTextContains('.toast-body', 'Saved successfully');
        
        $this->assertTrue($user->hasRole(Role::ROLE_ADMIN));
    }
}