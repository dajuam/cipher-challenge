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

    private function clean($str){
        $str = strtolower($str);
        $str = preg_replace('/[^a-z0-9 -]+/', '', $str);
        $str = str_replace(' ', '-', $str);
        return trim($str, '-');
    }

    /**
     * Uses core PHP function to replace characters.
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

    /**
     * Decrypt encrypted text from original text
     *
     * @param string $encryptedContent
     * @param string $originalContent
     * @return string
     */
    private function decryptCoreV2($encryptedContent, $originalContent) {
        // The expected mapper
        $expectedCollection = array();
        // Look for tentative patterns
        $plainCollection = explode(".]", $originalContent);
        $encryptedCollection = explode(".]", $encryptedContent);
        // Iterate over patterns
        foreach ($plainCollection as $pc) {
            foreach ($encryptedCollection as $ec) {
                if (strlen($ec) == strlen($pc)) {
                    // At this point Im sure that Iam in the right pattern
                    if (str_word_count($ec) == str_word_count($pc)) {
                        $charCollection = array_map('trim', explode(' ', $pc));
                        foreach(array_map('trim', explode(' ', $ec)) as $fk => $encryptedWord) {
                            if ($encryptedWord != "") {
                                $encryptedWordCollection = str_split($this->clean($encryptedWord));
                                $originalWordCollection = str_split($this->clean($charCollection[$fk]));
                                // I start to build the mapper
                                foreach($encryptedWordCollection as $sk => $letter) {
                                    if (!array_key_exists($letter, $expectedCollection)) {
                                        $expectedCollection[$letter] = $originalWordCollection[$sk];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // I build all the cases for upper and lower case words
        $finalUppercase = array_change_key_case(array_map('strtoupper', $expectedCollection), CASE_UPPER);
        $finalCollection = array_merge($expectedCollection, $finalUppercase);
        $finalString = strtr($encryptedContent, $finalCollection);
        return $finalString;
    }

    public function softDecryptTextFromSource($subject, $source) {
        return $this->decryptCoreV2($subject, $source);
    }

    /**
     * @deprecated
     */
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
            $encryptedFileContent = file_get_contents($data[$name]);
            if ($name == EncryptionType::SOFT_ENCRYPTED) {
                //$dt = $this->softDecryptText($fileContent);
                $dt = $this->softDecryptTextFromSource($encryptedFileContent, file_get_contents($data["Plain"]));
            }
            if ($name == EncryptionType::HARD_ENCRYPTED)
                $dt = $this->hardDecryptText($encryptedFileContent);
            return $this->generateTextFile($dt, $name . 'Output.txt');
        }
    }
}