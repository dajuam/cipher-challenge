<?php

namespace ChallengeBundle\Tests;

use PHPUnit\Framework\TestCase;
use ChallengeBundle\Service\ChallengeService;

class ChallengeServiceTest extends TestCase
{
    public function testSoftDecrypt() {
        $cs = new ChallengeService();
        $decrypted = $cs->softDecryptText("JXN PLQTQYGN");
        $this->assertEquals("THE PROLOGUE", $decrypted);
    }

    public function testHardDecrypt() {
        $cs = new ChallengeService();
        $decrypted = $cs->hardDecryptText("Ml. bur Mld. Dbmdb");
        $this->assertEquals("Mr. and Mrs. Samsa", $decrypted);
    }

    public function testSoftDecryptV2() {
        $cs = new ChallengeService();
        $encrypted = "Ml. bur Mld. Dbmdb";
        $original = "Mr. and Mrs. Samsa";
        $decrypted = $cs->softDecryptTextFromSource($encrypted, $original);
        $this->assertEquals($original, $decrypted);
    }
}