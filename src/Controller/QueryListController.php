<?php

namespace App\Controller;

use App\Entity\AddressBook;
use App\Entity\QueryList;
use App\Entity\User;
use App\Form\QueryListType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class QueryListController extends AbstractController
{
    private Security $security;

    private EntityManagerInterface $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/querylist/share/{id}", name="querylist_share" , methods={"POST", "GET"})
     */
    public function share(Request $request, AddressBook $addressBook): RedirectResponse|Response
    {
        $sender = $this->security->getUser();
        $form = $this->createForm(QueryListType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $querylist = $form->getData();
            $receiver = $querylist->getReceiver();
            $receiver = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $receiver]);
            if ($receiver->getId() !== $sender->getId()) {
                $queryCheck = $this->entityManager
                    ->getRepository(QueryList::class)
                    ->findOneBy([
                        'receiver' => $receiver,
                        'sender' => $sender,
                        'addressRecord' => $addressBook,
                        'sendStatus' => true,
                        'receiveStatus' => null,
                    ]);
                if (null === $queryCheck) {
                    $querylist->setReceiver($receiver);
                    $querylist->setSender($sender);
                    $querylist->setAddressRecord($addressBook);
                    $querylist->setSendStatus(true);
                    $this->entityManager->persist($querylist);
                    $this->entityManager->flush();
                    $this->addFlash('success', 'You have successfully shared a contact.');

                    return $this->redirectToRoute('addresses');
                }
                $form->addError(new FormError('You already shared this contact.'));
            }
            $form->addError(new FormError('You can\'t share contact with yourself'));
        }

        return $this->render('query_list/share_query.html.twig', ['form' => $form->createView(), 'addressBook' => $addressBook]);
    }

    /**
     * @Route("/query_list/all", name="users_querylists")
     */
    public function all_querylist(): Response
    {
        $userId = $this->security->getUser()->getId();
        $receivedQueries = $this->entityManager->getRepository(QueryList::class)->findBy(['receiver' => $userId, 'sendStatus' => true]);
        $sharedQueries = $this->entityManager->getRepository(QueryList::class)->findBy(['sender' => $userId]);

        return $this->render('query_list/all.html.twig', ['sharedQueries' => $sharedQueries, 'receivedQueries' => $receivedQueries]);
    }

    /**
     * @Route("/query_list/{id}/show_received", name="query_list/show_received")
     */
    public function showReceived(QueryList $queryList): Response
    {
        if ($this->security->getUser() !== $queryList->getReceiver()) {
            throw new AccessDeniedException();
        }

        return $this->render('query_list/show_received.html.twig', ['querylist' => $queryList]);
    }

    /**
     * @Route("/query_list/{id}/show_sended", name="query_list/show_sended")
     */
    public function showSended(QueryList $queryList): Response
    {
        if ($this->security->getUser() !== $queryList->getSender()) {
            throw new AccessDeniedException();
        }

        return $this->render('query_list/show_sended.html.twig', ['querylist' => $queryList]);
    }

    /**
     * @Route("/query_list/{id}/cancel_share", name="query_list/cancel_share" , methods={"POST"})
     */
    public function cancelShare(QueryList $queryList): RedirectResponse
    {
        $user = $this->security->getUser();
        if ($user === $queryList->getSender() && true === $queryList->getSendStatus() && null === $queryList->getReceiveStatus()) {
            $queryList->setSendStatus(!$queryList->getSendStatus());
            $this->entityManager->persist($queryList);
            $this->entityManager->flush();
            $this->addFlash('success', 'You have canceled your sharing.');
        }

        return $this->redirectToRoute('users_querylists');
    }

    /**
     * @Route("/query_list/{id}/{action}", name="query_list/resolve", methods={"POST"})
     */
    public function resolveQuery(QueryList $queryList, string $action): RedirectResponse
    {
        if ($queryList->getSendStatus() && null === $queryList->getReceiveStatus()) {
            $queryList->setReceiveStatus(false);
            if ('Accept' === $action) {
                $queryList->setReceiveStatus(true);
                $addressBook = new AddressBook();
                $addressBook->setName($queryList->getAddressRecord()->getName());
                $addressBook->setUser($queryList->getReceiver());
                $addressBook->setNumber($queryList->getAddressRecord()->getNumber());
                $this->entityManager->persist($addressBook);
                $this->addFlash('success', 'Address was successfully added to you\'r Address Book');
            }
            $this->entityManager->persist($queryList);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('users_querylists');
    }
}
