<?php

namespace App\Controller;

use App\Entity\AddressBook;
use App\Form\AddressBookType;
use App\Repository\AddressBookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddressBookController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private AddressBookRepository $addressBookRepository;
    private Security $security;

    /**
     * @param EntityManagerInterface $entityManager
     * @param AddressBookRepository $addressBookRepository
     * @param Security $security
     */
    public function __construct(EntityManagerInterface $entityManager, AddressBookRepository $addressBookRepository, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->addressBookRepository = $addressBookRepository;
        $this->security = $security;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param AddressBookRepository $addressBookRepository
     */


    /**
     * @Route("/addresses", name="addresses")
     */
    public function index(): Response
    {
        $list = $this->addressBookRepository->findAll();
        return $this->render('address/index.html.twig', ['list' => $list]);
    }

    /**
     * @Route("/address/add", name="address/add")
     */
    public function add(Request $request): Response
    {
        $form = $this->createForm(AddressBookType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $addressBook = $form->getData();
            $addressBook->setUser($this->security->getUser());
            $this->entityManager->persist($addressBook);
            $this->entityManager->flush();
            $this->addFlash('success', 'Address has been successfully added');
            return $this->redirectToRoute('addresses');
        }
        return $this->render('address/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/address/{id}/edit", name="address/edit")
     */
    public function edit(Request $request, AddressBook $addressBook): Response
    {
        $form = $this->createForm(AddressBookType::class, $addressBook);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();
            $this->addFlash('success', 'Address has been successfully edited');
            return $this->redirectToRoute('addresses');
        }
        return $this->render('address/edit.html.twig', ['form' => $form->createView(), 'addressBook' => $addressBook]);
    }

    /**
     * @Route("/address/{id}/show", name="address/show")
     */
    public function show(AddressBook $addressBook): Response
    {
        return $this->render('address/show.html.twig', ['addressBook' => $addressBook]);
    }

    /**
     * @Route("/address/{id}/delete", name="address/delete", methods={"POST"})
     */
    public function delete(Request $request)
    {
        $addressBook = $this->addressBookRepository->find($request->get('id'));
        if (null !== $addressBook) {
            $this->entityManager->remove($addressBook);
            $this->entityManager->flush();
            $this->addFlash('success', 'Address has been successfully deleted');
            return $this->redirectToRoute('addresses');
        }
    }
}
