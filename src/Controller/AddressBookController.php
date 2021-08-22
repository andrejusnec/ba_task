<?php

namespace App\Controller;

use App\Entity\AddressBook;
use App\Entity\QueryList;
use App\Entity\User;
use App\Form\AddressBookType;
use App\Repository\AddressBookRepository;
use App\Repository\UserRepository;
use App\Services\AddressHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
    public function all(): Response
    {
        $allAddressbooks = $this->addressBookRepository->findBy(['user' => $this->security->getUser()]);
        return $this->render('address/index.html.twig', ['list' => $allAddressbooks]);
    }

    /**
     * @Route("/address/add", name="address/add", methods={"POST", "GET"})
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
     * @Route("/address/{id}/edit", name="address/edit", methods={"PUT", "POST", "GET"})
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
     * @Route("/address/{id}/show", name="address/show", methods={"GET"})
     */
    public function show($id): Response
    {
        $currentUser = $this->security->getUser();
        $addressBook = $this->entityManager->getRepository(AddressBook::class)->findOneBy(['id' => $id]);
        if($addressBook->getUser()->getId() !== $currentUser->getId()) {
            Throw new AccessDeniedException;
        }
        return $this->render('address/show.html.twig', ['addressBook' => $addressBook ]);
    }

    /**
     * @Route("/address/{id}/delete", name="address/delete", methods={"DELETE"})
     */
    public function delete(Request $request, AddressHelper $addressHelper): RedirectResponse
    {
        $addressBook = $this->addressBookRepository->find($request->get('id'));
        $querylists = $this->entityManager->getRepository(QueryList::class)->findBy(['addressRecord' => $addressBook]);
        if (null !== $addressBook && $addressHelper->checkForActiveQueryLists($querylists)) {
            foreach ($querylists as $query){
                $this->entityManager->remove($query);
            }
            $this->entityManager->remove($addressBook);
            $this->entityManager->flush();
            $this->addFlash('success', 'Address has been successfully deleted');
            return $this->redirectToRoute('addresses');
        }
        $this->addFlash('error', 'Contact is missing or you have active Share of this contact.');
        return $this->redirectToRoute('address/show', ['id' => $addressBook->getId()]);
    }
}
