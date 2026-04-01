<?php

namespace App\Tests\Application\Controller;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class MessageControllerTest extends AbstractApplicationTestCase
{
    public function testMessageList(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('message-list'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Messages');
    }

    public function testMessageView(): void
    {
        $this->logInAdmin();
        
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        /** @var Message $message */
        $message = $em->getRepository(Message::class)->findOneBy(['name' => 'Test User 1']);
        
        $this->client->request('GET', $this->router->generate('message-view', ['id' => $message->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', $message->getSubject());
        
        $em->refresh($message);
        $this->assertTrue($message->isSeen());
    }

    public function testMessageViewNotFound(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('message-view', ['id' => 99999]));
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testMessageDelete(): void
    {
        $this->logInAdmin();
        
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        /** @var Message $message */
        $message = $em->getRepository(Message::class)->findOneBy(['name' => 'Test User 2']);
        $id = $message->getId();

        $this->client->request('POST', $this->router->generate('message-delete'), ['id' => [$id => 'on']]);
        
        $this->assertResponseRedirects($this->router->generate('message-list'));
        
        $em->clear();
        $this->assertNull($em->getRepository(Message::class)->find($id));
    }

    public function testMessageDeleteSingleId(): void
    {
        $this->logInAdmin();
        
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        /** @var Message $message */
        $message = $em->getRepository(Message::class)->findOneBy(['name' => 'Test User 4']);
        $id = $message->getId();

        $this->client->request('POST', $this->router->generate('message-delete'), ['id' => $id]);
        
        $this->assertResponseRedirects($this->router->generate('message-list'));
        
        $em->clear();
        $this->assertNull($em->getRepository(Message::class)->find($id));
    }

    public function testMessageDeleteNotFound(): void
    {
        $this->logInAdmin();
        $this->client->request('POST', $this->router->generate('message-delete'), ['id' => [99999 => 'on']]);
        $this->assertResponseRedirects($this->router->generate('message-list'));
    }

    public function testMessageMarkSeen(): void
    {
        $this->logInAdmin();
        
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        /** @var Message $message */
        $message = $em->getRepository(Message::class)->findOneBy(['name' => 'Test User 3']);
        $id = $message->getId();

        $this->client->request('POST', $this->router->generate('message-seen'), ['id' => [$id => 'on']]);
        
        $this->assertResponseRedirects($this->router->generate('message-list'));
        
        $em->refresh($message);
        $this->assertTrue($message->isSeen());
    }

    public function testMessageMarkSeenInvalidId(): void
    {
        $this->logInAdmin();
        // Submit a non-array ID to trigger the NotFoundHttpException
        $this->client->request('POST', $this->router->generate('message-seen'), ['id' => 123]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
