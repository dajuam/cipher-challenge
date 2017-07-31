<?php

namespace ChallengeBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertGreaterThan(0, $crawler->filter('html:contains("uploaders below to decrypt")')->count());
    }

    public function testSoftEncryptedResponseAttachment() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $form = $crawler->selectButton('soft_SaveSoft')->form();
        $form['soft[SoftEncrypted]']->upload('originals/encrypted.txt');
        $form['soft[Plain]']->upload('originals/plain.txt');
        $client->submit($form);
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Disposition',
                'attachment; filename="SoftEncryptedOutput.txt"'
            ), 'The Content-Disposition must be "attachment; filename=SoftEncryptedOutput.txt"'
        );
    }

    public function testHardEncryptedResponseAttachment() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $form = $crawler->selectButton('hard_SaveHard')->form();
        $form['hard[HardEncrypted]']->upload('originals/encrypted_hard.txt');
        $client->submit($form);
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Disposition',
                'attachment; filename="HardEncryptedOutput.txt"'
                ), 'The Content-Disposition must be "attachment; filename=HardEncryptedOutput.txt"'
            );
    }
}
