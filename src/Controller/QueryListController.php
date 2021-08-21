<?php

namespace App\Controller;

use App\Entity\AddressBook;
use App\Entity\QueryList;
use App\Entity\User;
use App\Form\QueryListType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\CustomAuthenticationFailureHandler;

class QueryListController extends AbstractController
{
    private Security $security;

    private EntityManagerInterface $entityManager;

    /**
     * @param Security $security
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/querylist/share/{addressBook}", name="querylist_share")
     */
    public function share(Request $request, AddressBook $addressBook): RedirectResponse|Response
    {
        $addressBook = $this->entityManager->getRepository(AddressBook::class)->findOneBy(['id' => $addressBook]);
        $sender = $this->security->getUser();
        $querylist = new QueryList();
        $querylist->setSender($sender);
        $querylist->setAddressRecord($addressBook);
        $form = $this->createForm(QueryListType::class, $querylist);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $querylist = $form->getData();
            $receiver = $querylist->getReceiver();
            $receiver = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $receiver]);
            $queryCheck = $this->entityManager
                ->getRepository(QueryList::class)
                ->findOneBy([
                    'receiver' => $receiver,
                    'sender' => $sender,
                    'addressRecord' => $addressBook,
                    'sendStatus' => true,
                    'receiveStatus' => null]);
            if (null !== $receiver && null === $queryCheck) {
                $querylist->setReceiver($receiver);
                $querylist->setSender($sender);
                $querylist->setAddressRecord($addressBook);
                $querylist->setSendStatus(true);
                $this->entityManager->persist($querylist);
                $this->entityManager->flush();
                $this->addFlash('success', 'You have successfully shared a contact.');
                return $this->redirectToRoute('addresses');
            }
            null !== $queryCheck ? $form->addError(new FormError('You already shared this contact.')) :
            $form->addError(new FormError('There is no user with this email.'));
        }
        return $this->render('query_list/share_query.html.twig', ['form' => $form->createView()]);
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

    public function showReceived(int $id): Response
    {
        $querylist = $this->entityManager->getRepository(QueryList::class)->find($id);
        //extra check
        return $this->render('query_list/show_received.html.twig', ['querylist' => $querylist]);
    }

    /**
     * @Route("/query_list/{id}/show_sended", name="query_list/show_sended")
     */
    public function showSended(int $id): Response
    {
        $querylist = $this->entityManager->getRepository(QueryList::class)->find($id);
        return $this->render('query_list/show_sended.html.twig', ['querylist' => $querylist]);
    }

    /**
     * @Route("/query_list/{id}/cancel_share", name="query_list/cancel_share")
     */
    public function cancelShare(int $id): RedirectResponse
    {
        $user = $this->security->getUser();
        $querylist = $this->entityManager->getRepository(QueryList::class)->findOneBy(['id' => $id, 'sender' => $user]);
        if (null !== $querylist && $querylist->getSendStatus() && null === $querylist->getReceiveStatus()) {
            $querylist->setSendingStatus(false);
            $this->entityManager->persist($querylist);
            $this->entityManager->flush();
            $this->addFlash('success', 'You have canceled your sharing.');
        }
        return $this->redirectToRoute('users_querylists');
    }

    /**
     * @Route("/query_list/{id}/{action}", name="query_list/resolve")
     */
    public function resolveQuery(int $id, string $action): RedirectResponse
    {
        $querylist = $this->entityManager->getRepository(QueryList::class)->find($id);
        if (null !== $querylist && $querylist->getSendStatus() && null === $querylist->getReceiveStatus()) {
            $querylist->setReceiveStatus(false);
            if ('Accept' === $action) {
                $querylist->setReceiveStatus(true);
                $addressBook = new AddressBook();
                $addressBook->setName($querylist->getAddressRecord()->getName());
                $addressBook->setUser($querylist->getReceiver());
                $addressBook->setNumber($querylist->getAddressRecord()->getNumber());
                $this->entityManager->persist($addressBook);
                $this->addFlash('success', 'Address was successfully added to you\'r Address Book');
            }
            $this->entityManager->persist($querylist);
            $this->entityManager->flush();
        }
        return $this->redirectToRoute("users_querylists");
    }

}
