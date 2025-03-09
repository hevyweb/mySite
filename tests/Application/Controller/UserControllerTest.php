<?php

namespace App\Tests\Application\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Type\Gender;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends AbstractApplicationTestCase
{
    public static function InvalidCredentialsDataProvider(): \Generator
    {
        yield 'empty' => ['', ''];
        yield 'null values' => [null, null];
        yield 'invalid username' => ['user_not_exist', 'test'];
        yield 'invalid password' => ['user', 'invalid password'];
        yield 'empty password' => ['user', ''];
        yield 'empty user' => ['', 'user'];
        yield 'sql injection' => ['user', '1\' OR \'1\' =\'1'];
    }

    public static function invalidUserDataProvider(): \Generator
    {
        yield 'blank email' => [
            ['edit_user[email]' => ''],
            'This value should not be blank.',
        ];

        yield 'invalid email' => [
            ['edit_user[email]' => 'invalid email address'],
            'This value is not a valid email address.',
        ];

        yield 'very long email' => [
            ['edit_user[email]' => str_repeat('a', 59) . '@a.com'],
            'This value is too long. It should have 64 characters or less.',
        ];

        yield 'blank first name' => [
            ['edit_user[firstName]' => ''],
            'This value should not be blank.',
        ];

        yield 'First name is too short' => [
            ['edit_user[firstName]' => 'a'],
            'This value is too short. It should have 2 characters or more.',
        ];

        yield 'first name too long' => [
            ['edit_user[firstName]' => str_repeat('a', 33)],
            'This value is too long. It should have 32 characters or less.',
        ];

        yield 'blank last name' => [
            ['edit_user[lastName]' => ''],
            'This value should not be blank.',
        ];

        yield 'Last name is too short' => [
            ['edit_user[lastName]' => 'a'],
            'This value is too short. It should have 2 characters or more.',
        ];

        yield 'last name too long' => [
            ['edit_user[lastName]' => str_repeat('a', 33)],
            'This value is too long. It should have 32 characters or less.',
        ];

        yield 'invalid birth date' => [
            ['edit_user[birthday]' => '27.13.1659'],
            'Please enter a valid birthdate.',
        ];

        yield 'Birthday out of the date range' => [
            ['edit_user[birthday]' => (new \DateTime('-101 year'))->format('d.m.Y')],
            'You cannot be older than 100 years.'
        ];

        yield 'Birthday is too close' => [
            ['edit_user[birthday]' => (new \DateTime('-6 year'))->format('d.m.Y')],
            'You must be at least 7 years old.'
        ];
    }

    public static function passwordChangeFailureDataProvider(): \Generator
    {
        yield 'empty current password' => [
            ['user_passwords[currentPassword]' => null],
            'This value should not be blank.',
        ];

        yield 'too big current password' => [
            ['user_passwords[currentPassword]' => str_repeat('a', 33)],
            'This value is too long. It should have 32 characters or less.',
        ];

        yield 'incorrect current password' => [
            ['user_passwords[currentPassword]' => 'this is not a password'],
            'Current password is incorrect.'
        ];

        yield 'empty new password' => [
            [
                'user_passwords[newPassword][first]' => null,
                'user_passwords[newPassword][second]' => null,
            ],
            'This value should not be blank.',
        ];

        yield 'too long new password' => [
            [
                'user_passwords[newPassword][first]' => str_repeat('a', 33),
                'user_passwords[newPassword][second]' => str_repeat('a', 33),
            ],
            'This value is too long. It should have 32 characters or less.',
        ];

        yield 'passwords do not match' => [
            [
                'user_passwords[newPassword][first]' => str_repeat('a', 32),
                'user_passwords[newPassword][second]' => str_repeat('b', 32),
            ],
            'The password fields must match.',
        ];
    }

    public static function registerFailureDataProvider(): \Generator
    {
        yield 'Username is missing' => [
            ['registration[username]' => null],
            'This value should not be blank.',
        ];

        yield 'Username is too short' => [
            ['registration[username]' => 'a'],
            'This value is too short. It should have 3 characters or more.',
        ];

        yield 'Username is too long' => [
            ['registration[username]' => str_repeat('a', 33)],
            'This value is too long. It should have 32 characters or less.',
        ];

        yield 'Username has illegal characters' => [
            ['registration[username]' => 'test space'],
            'Username should contain only letters, numbers, minus sign or underscore.',
        ];

        yield 'Email is missing' => [
            ['registration[email]' => null],
            'This value should not be blank.',
        ];

        yield 'Email is incorrect' => [
            ['registration[email]' => 'dummy value'],
            'This value is not a valid email address.',
        ];

        yield 'Email is too long' => [
            ['registration[email]' => str_repeat('a', 56) . '@fake.com'],
            'This value is too long. It should have 64 characters or less.',
        ];

        yield 'Email is already registered' => [
            ['registration[email]' => 'user@fake.com'],
            'This email has been already registered.',
        ];

        yield 'Password is empty' => [
            [
                'registration[password][first]' => null,
                'registration[password][second]' => null,
            ],
            'This value should not be blank.',
        ];

        yield 'Passwords do not match' => [
            [
                'registration[password][first]' => 'first',
                'registration[password][second]' => 'second',
            ],
            'The password fields must match.',
        ];

        yield 'Password is too long' => [
            [
                'registration[password][first]' => str_repeat('a', 33),
                'registration[password][second]' => str_repeat('a', 33),
            ],
            'This value is too long. It should have 32 characters or less.',
        ];

        yield 'Password is too short' => [
            [
                'registration[password][first]' => str_repeat('a', 7),
                'registration[password][second]' => str_repeat('a', 7),
            ],
            'This value is too short. It should have 8 characters or more.',
        ];

        yield 'First name is empty' => [
            ['registration[firstName]' => null],
            'This value should not be blank.',
        ];

        yield 'First name is too short' => [
            ['registration[firstName]' => 'a'],
            'This value is too short. It should have 2 characters or more.',
        ];

        yield 'First name is too long' => [
            ['registration[firstName]' => str_repeat('a', 33)],
            'This value is too long. It should have 32 characters or less.',
        ];

        yield 'Last name is empty' => [
            ['registration[lastName]' => null],
            'This value should not be blank.',
        ];

        yield 'Last name is too short' => [
            ['registration[lastName]' => 'a'],
            'This value is too short. It should have 2 characters or more.',
        ];

        yield 'Last name is too long' => [
            ['registration[lastName]' => str_repeat('a', 33)],
            'This value is too long. It should have 32 characters or less.',
        ];

        yield 'Invalid birthday' => [
            ['registration[birthday]' => '99.32.2029'],
            'Please enter a valid birthdate.',
        ];

        yield 'Birthday out of the date range' => [
            ['registration[birthday]' => (new \DateTime('-101 year'))->format('d.m.Y')],
            'You cannot be older than 100 years.',
        ];

        yield 'Birthday is too close' => [
            ['registration[birthday]' => (new \DateTime('-6 year'))->format('d.m.Y')],
            'You must be at least 7 years old.',
        ];
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
     */
    public function testLoginFailed(?string $username, ?string $password): void
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
        $user = $this->getUser('user');
        $this->assertEquals('user@fake.com', $user->getEmail());
        $this->assertEquals('New firstname', $user->getFirstName());
        $this->assertEquals('New lastname', $user->getLastName());
        $this->assertEquals('01.01.1961', $user->getBirthday()->format('d.m.Y'));
        $this->assertEquals(Gender::FEMALE, $user->getSex());
    }

    /**
     * @param array<string, string|null> $invalidData
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
        $this->assertFormValue('form[name="edit_user"]', 'edit_user[sex]', (string) Gender::FEMALE);
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
     * @param array<string, string|null> $invalidData
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
            $this->assertTrue($checkbox instanceof ChoiceFormField);
            /**
             * @var ChoiceFormField $checkbox
             */
            $checkbox->tick();
        }

        $this->client->submit($form);

        $user = $this->getUser('user');

        $this->assertSelectorTextContains('.toast-body', 'Saved successfully');

        $this->assertTrue($user->hasRole(Role::ROLE_ADMIN));
    }

    public function testRegisterSuccess(): void
    {
        $this->client->request('GET', $this->router->generate('user-registration'));

        $data = [
            'registration[username]' => 'new-user',
            'registration[email]' => 'new-user-email@fake.com',
            'registration[password][first]' => 'password',
            'registration[password][second]' => 'password',
            'registration[firstName]' => 'First name',
            'registration[lastName]' => 'Last name',
            'registration[birthday]' => (new \DateTime('-7 years'))
                ->sub(new \DateInterval('P1M'))
                ->format('d.m.Y'),
            'registration[sex]' => Gender::FEMALE,
        ];

        $this->client->submitForm('Sign up', $data);

        try {
            $user = $this->getUser('new-user');
        } catch (UserNotFoundException $exception) {
            $this->fail('User is not saved.');
        }
        $this->assertEquals($data['registration[username]'], $user->getUsername());
        $this->assertEquals($data['registration[email]'], $user->getEmail());
        $this->assertEquals($data['registration[firstName]'], $user->getFirstName());
        $this->assertEquals($data['registration[lastName]'], $user->getLastName());
        $this->assertEquals($data['registration[birthday]'], $user->getBirthday()->format('d.m.Y'));
        $this->assertEquals($data['registration[sex]'], $user->getSex());
        $hasher = $this->getContainer()->get(UserPasswordHasherInterface::class);

        $this->assertTrue($hasher->isPasswordValid($user, 'password'));
    }

    /**
     * @param array<string, string|null> $invalidData
     * @dataProvider registerFailureDataProvider
     */
    public function testRegisterFailure(array $invalidData, string $errorMessage): void
    {
        $this->client->request('GET', $this->router->generate('user-registration'));

        $validaData = [
            'registration[username]' => 'new-user',
            'registration[email]' => 'new-user-email@fake.com',
            'registration[password][first]' => 'password',
            'registration[password][second]' => 'password',
            'registration[firstName]' => 'First name',
            'registration[lastName]' => 'Last name',
            'registration[birthday]' => '24.02.1999',
            'registration[sex]' => Gender::FEMALE,
        ];

        $data = array_merge($validaData, $invalidData);

        $this->client->submitForm('Sign up', $data);
        $this->assertSelectorTextContains('.form_validation_error', $errorMessage);
    }

    public function testSetWrongGenderOnRegister(): void
    {
        $this->client->request('GET', $this->router->generate('user-registration'));

        $select = $this->client->getCrawler()->filter('#registration_sex')->getNode(0);
        $invalidOption = $select->ownerDocument->createElement('option', 'invalid gender');
        $invalidOption->setAttribute('value', '3');
        $select->appendChild($invalidOption);

        $this->client->submitForm('Sign up', [
            'registration[username]' => 'new-user',
            'registration[email]' => 'new-user-email@fake.com',
            'registration[password][first]' => 'password',
            'registration[password][second]' => 'password',
            'registration[firstName]' => 'First name',
            'registration[lastName]' => 'Last name',
            'registration[birthday]' => '24.02.1999',
            'registration[sex]' => 3,
        ]);

        $this->assertSelectorTextContains('.form_validation_error', 'The selected choice is invalid.');
    }

    public function testRegisterLoggedInUser(): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('user-registration'));
        $this->assertResponseRedirects($this->router->generate('user-edit-general'));
    }

    public function testLogOutUser(): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('user-logout'));
        $this->assertResponseRedirects($this->router->generate('home'));
        $session = $this->client->getRequest()->getSession();
        $this->assertFalse($session->has('_security_main'), 'User is logged in');
    }

    public function testConfirmEmail(): void
    {
        $token = str_repeat('a', 64);
        $user = $this->getUser('user');
        $user->setActive(false)
            ->setEmailConfirm($token);
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager->flush();

        $this->client->request('GET', $this->router->generate('user-confirm-email', [
            'token' => $token,
        ]));

        $entityManager->refresh($user);
        $this->assertTrue($user->getActive());
        $this->assertNull($user->getEmailConfirm());
    }

    public function testConfirmEmailUserLoggedIn(): void
    {
        $this->logInUser();
        $token = str_repeat('a', 64);
        $this->client->request('GET', $this->router->generate('user-confirm-email', [
            'token' => $token,
        ]));

        $this->assertResponseRedirects($this->router->generate('user-edit-general'));
    }

    public function testConfirmEmailInvalidCode(): void
    {
        $token = str_repeat('a', 64);
        $this->client->request('GET', $this->router->generate('user-confirm-email', [
            'token' => $token,
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testRecoverPasswordWhenLoggedIn(): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('user-recover-password'));
        $this->assertResponseRedirects($this->router->generate('user-edit-general'));
    }

    public function testRecoverPasswordSuccess(): void
    {
        $this->client->request('GET', $this->router->generate('user-recover-password'));
        $this->client->submitForm('Recover password', [
            'recover_password[email]' => 'user@fake.com',
        ]);
        $this->assertResponseRedirects($this->router->generate('home'));
        $user = $this->getUser('user');
        $this->assertNotNull($user->getRecovery());
        $this->assertNotNull($user->getRecoveredAt());
    }

    public function testResetPasswordSuccess(): void
    {
        $token = str_repeat('aA-zA123', 8);

        $user = $this->getUser('user');
        $user->setRecovery($token);
        $user->setRecoveredAt(new \DateTime());
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        /**
         * @var EntityManagerInterface $em
         */
        $em->flush();
        $oldPassword = $user->getPassword();
        $updateDate = $user->getUpdatedAt();

        $this->client->request('GET', $this->router->generate('user-reset-password', ['token' => $token]));
        $this->assertResponseRedirects($this->router->generate('home'));
        $em->refresh($user);

        $this->assertTrue($oldPassword !== $user->getPassword());
        $this->assertTrue($updateDate !== $user->getUpdatedAt());
        $this->assertNull($user->getRecovery());
        $this->assertNull($user->getRecoveredAt());
    }

    public function testConfirmEmailChangeSuccess(): void
    {
        $this->logInUser();
        $token = str_repeat('aA-zA123', 8);
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $em->flush();
        $this->client->request('GET', $this->router->generate('user-new-email-confirm', ['token' => $token]));
        $user = $this->getUser('user');

        $this->assertEquals('user@fake.com', $user->getEmail());
        $this->client->request('GET', $this->router->generate('user-old-email-confirm', ['token' => $token]));
        $user = $this->getUser('user');

        $this->assertEquals('test2@fake.com', $user->getEmail());
    }
}