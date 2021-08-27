<?php

namespace App\Controller;

use App\Form\UserType;
use App\Services\UploaderHelper;
use League\Flysystem\FilesystemException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route ("/user_profile", name="user_edit_profile")
     *
     * @throws FilesystemException
     */
    public function editUserProfile(Request $request, UserPasswordHasherInterface $passwordHasher, UploaderHelper $uploaderHelper): Response
    {
        $form = $this->createForm(UserType::class, $this->getUser());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form['oldPassword']->getData();
            $user = $form->getData();
            if (null !== $form['plainPassword']->getData()) {
                if (null === $oldPassword || !$passwordHasher->isPasswordValid($user, $oldPassword)) {
                    $form->addError(new FormError('Old password is invalid.'));

                    return $this->render('user/index.html.twig', ['user_form' => $form->createView()]);
                }
                $encodedPassword = $passwordHasher->hashPassword(
                    $user,
                    $form['plainPassword']->getData()
                );
                $user->setPassword($encodedPassword);
            }
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadImage($uploadedFile, $user->getImageFileName(), UploaderHelper::USER_IMAGE);
                $user->setImageFileName($newFilename);
            }
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush($user);
            $this->addFlash('success', 'You have successfully edited you\'r profile.');

            return $this->redirectToRoute('addresses');
        }

        return $this->render('user/index.html.twig', ['user_form' => $form->createView()]);
    }
}
