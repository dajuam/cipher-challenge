<?php

namespace ChallengeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use ChallengeBundle\Type\EncryptionType;

/**
 * @author Juan Pablo Martinez
 */
class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET","POST"})
     */
    public function indexAction(Request $request)
    {
        $formSoft = $this->get('form.factory')->createNamedBuilder('soft')
            ->add('Plain', FileType::class, array('label' => 'Plain (plain.txt)','required' => true))
            ->add('SoftEncrypted', FileType::class, array('label' => 'Soft Encrypted (encrypted.txt)','required' => true))
            ->add('SaveSoft', SubmitType::class, array('label' => 'Generate file', 'attr' => array('class' => 'btn btn-primary')))
        ->getForm();
        $formHard = $this->get('form.factory')->createNamedBuilder('hard')
            ->add('HardEncrypted', FileType::class, array('label' => 'Hard Encrypted (encrypted_hard.txt)','required' => true))
            ->add('SaveHard', SubmitType::class, array('label' => 'Generate file', 'attr' => array('class' => 'btn btn-primary')))
        ->getForm();
        if ($request->isMethod('POST')) {
            $cs = $this->get('challenge.service');
            $formSoft->handleRequest($request);
            $formHard->handleRequest($request);
            if ($formSoft->isSubmitted() && $formSoft->getData()[EncryptionType::SOFT_ENCRYPTED] != null && $formSoft->isValid()) {
                return $cs->genericDecryp($formSoft->getData(), EncryptionType::SOFT_ENCRYPTED);
            }
            if ($formHard->isSubmitted() && $formHard->getData()[EncryptionType::HARD_ENCRYPTED] != null) 
                return $cs->genericDecryp($formHard->getData(), EncryptionType::HARD_ENCRYPTED);
        }
        return $this->render('ChallengeBundle:Default:index.html.twig', array(
            'form_soft' => $formSoft->createView(),
            'form_hard' => $formHard->createView()
        ));
    }
}
