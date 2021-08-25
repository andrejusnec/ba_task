<?php

namespace App\Controller;

use App\Entity\AddressBook;
use App\Entity\QueryList;
use App\Form\AddressBookType;
use App\Repository\AddressBookRepository;
use App\Services\AddressHelper;
use App\Services\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use League\Flysystem\FilesystemException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class AddressBookController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private AddressBookRepository $addressBookRepository;
    private Security $security;

    /**
     * @IsGranted("ROLE_USER")
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
    public function all(Request $request, PaginatorInterface $paginator): Response
    {
        $q = $request->query->get('q');
        $queryBuilder = $this->addressBookRepository->getAllWithSearchQueryBuilder($q, $this->security->getUser());
        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1),
            10
        );
        //$allAddressbooks = $this->addressBookRepository->findBy(['user' => $this->security->getUser()]);

        return $this->render('address/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * @Route("/address/add", name="address/add", methods={"POST", "GET"})
     * @throws FilesystemException
     */
    public function add(Request $request, UploaderHelper $uploaderHelper): Response
    {
        $form = $this->createForm(AddressBookType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $addressBook = $form->getData();
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadAddressBookImage($uploadedFile, $addressBook->getImageFileName());
                $addressBook->setImageFileName($newFilename);
            }
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
     * @throws FilesystemException
     */
    public function edit(Request $request, AddressBook $addressBook, UploaderHelper $uploaderHelper): Response
    {
        $form = $this->createForm(AddressBookType::class, $addressBook);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $addressBook = $form->getData();
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadAddressBookImage($uploadedFile, $addressBook->getImageFileName());
                $addressBook->setImageFileName($newFilename);
            }
            $this->entityManager->persist($addressBook);
            $this->entityManager->flush();
            $this->addFlash('success', 'Address has been successfully edited');

            return $this->redirectToRoute('addresses');
        }

        return $this->render('address/edit.html.twig', ['form' => $form->createView(), 'addressBook' => $addressBook]);
    }

    /**
     * @Route("/address/{id}/show", name="address/show", methods={"GET"})
     */
    public function show($id, CacheManager $imagineCacheManager): Response
    {
        $currentUser = $this->security->getUser();
        $addressBook = $this->entityManager->getRepository(AddressBook::class)->findOneBy(['id' => $id]);
        if ($addressBook->getUser()->getId() !== $currentUser->getId()) {
            throw new AccessDeniedException();
        }
        return $this->render('address/show.html.twig', ['addressBook' => $addressBook]);
    }

    /**
     * @Route("/address/{id}/delete", name="address/delete", methods={"DELETE", "POST"})
     */
    public function delete(Request $request, AddressHelper $addressHelper): RedirectResponse
    {
        $addressBook = $this->addressBookRepository->find($request->get('id'));
        $querylists = $this->entityManager->getRepository(QueryList::class)->findBy(['addressRecord' => $addressBook]);
        if (null !== $addressBook && $addressHelper->checkForActiveQueryLists($querylists)) {
            foreach ($querylists as $query) {
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
