<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddressController extends AbstractController
{
    private AddressRepository $addressRepository;
    private EntityManagerInterface $entityManager;

    /**
     * @param AddressRepository $addressRepository
     */
    public function __construct(AddressRepository $addressRepository, EntityManagerInterface $entityManager)
    {
        $this->addressRepository = $addressRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/address", name="address")
     */
    public function index(): Response
    {
        return $this->render('address/index.html.twig', [
            'controller_name' => 'AddressController',
        ]);
    }

    /**
     * @Route("/address/add", name="address/add")
     */
    public function add(Request $request) : Response
    {
        $form = $this->createForm(AddressType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();
            $this->addFlash('success', 'Address has been successfully added');
            return $this->redirectToRoute('user');
        }
        return $this->render('address/add.html.twig', ['form' => $form->createView()]);
    }
}
