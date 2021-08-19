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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\CustomAuthenticationFailureHandler;

class QueryListController extends AbstractController
{
    private Security $security;

    /**
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    /**
     * @Route("/querylist/share/{addressBook}", name="querylist_share")
     */
    public function share(Request $request, AddressBook $addressBook, EntityManagerInterface $entityManager)
    {
        $addressBook = $entityManager->getRepository(AddressBook::class)->findOneBy(['id' => $addressBook]);
        $sender = $this->security->getUser();
        $querylist = new QueryList();
        $querylist->setSender($sender);
        $querylist->setAddressRecord($addressBook);
        $form = $this->createForm(QueryListType::class, $querylist);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $querylist = $form->getData();
            $receiver = $querylist->getReceiver();
            $receiver = $entityManager->getRepository(User::class)->findOneBy(['email' => $receiver]);
            if (null !== $receiver) {
                $querylist->setReceiver($receiver);
                $querylist->setSender($sender);
                $querylist->setAddressRecord($addressBook);
                $querylist->setSendStatus(true);
                $querylist->setReceiveStatus(false);
                $entityManager->persist($querylist);
                $entityManager->flush();
                $this->addFlash('success', 'You have successfully shared a contact.');
                return $this->redirectToRoute('addresses');
            }
            $form->addError(new FormError('There is no user with this email.'));
        }
        return $this->render('query_list/share_query.html.twig', ['form' => $form->createView()]);
    }
}
