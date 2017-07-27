<?php

namespace ChallengeBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ChallengeBundle\Type\EncryptionType;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @author Juan Pablo Martinez
 */
class ChallengeService
{
    use ContainerAwareTrait;

    // Master pattern
    const ALPHABETICAL_VALUES = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

    /**
     * Makes the magic. Uses core PHP function to replace characters.
     * 
     * @param array $pattern
     * @param string $subject
     * @return string
     */
    private function decryptCore($pattern, $subject) {
        $keys = array_merge($pattern, array_map('strtolower', $pattern));
        $values = array_merge(self::ALPHABETICAL_VALUES, array_map('strtolower', self::ALPHABETICAL_VALUES));
        $decryptMapper = array_combine($values, $keys);
        return strtr($subject, $decryptMapper);
    }

    public function softDecryptText($subject) {
        $pattern = array('Z', 'Y', 'X', 'W', 'F', 'V', 'U', 'B', 'C', 'T', 'S', 'R', 'D', 'E', 'Q', 'P', 'O', 'N', 'M', 'L', 'K', 'J', 'I', 'H', 'G', 'A');
        return $this->decryptCore($pattern, $subject);
    }

    public function hardDecryptText($subject) {
        $pattern = array('T', 'A', 'K', 'S', 'Z', 'Y', 'W', 'L', 'Q', 'J', 'B', 'R', 'M', 'V', 'G', 'O', 'C', 'D', 'U', 'I', 'N', 'P', 'F', 'X', 'H', 'E');
        return $this->decryptCore($pattern, $subject);
    }

    /*
     * Generates response for text files
     */
    public function generateTextFile($text, $fileName) {
        $response = new Response($text);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /*
     * Manager to handle decryptions
     */
    public function genericDecryp($data, $name) {
        if ($data[$name] != null) {
            $fileContent = file_get_contents($data[$name]);
            if ($name == EncryptionType::SOFT_ENCRYPTED)
                $dt = $this->softDecryptText($fileContent);
            if ($name == EncryptionType::HARD_ENCRYPTED)
                $dt = $this->hardDecryptText($fileContent);
            return $this->generateTextFile($dt, $name . 'Output.txt');
        }
    }
}