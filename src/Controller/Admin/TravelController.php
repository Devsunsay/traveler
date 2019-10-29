<?php

namespace App\Controller\Admin;

use App\Entity\Picture;
use App\Entity\Travel;
use App\Form\TravelType;
use App\Repository\TravelRepository;
use App\Services\FileLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/travel")
 */
class TravelController extends AbstractController
{
    /**
     * @Route("/", name="travel_index", methods={"GET"})
     * @param TravelRepository $travelRepository
     * @return Response
     */
    public function index(TravelRepository $travelRepository): Response
    {
        return $this->render('travel/index.html.twig', [
            'travels' => $travelRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="travel_new", methods={"GET","POST"})
     * @param Request $request
     * @param FileLoader $fileLoader
     * @return Response
     */
    public function new(Request $request, FileLoader $fileLoader): Response
    {
        $travel = new Travel();
        $form = $this->createForm(TravelType::class, $travel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $pictureFile */
            $pictureFiles = $form['pictures']->getData();

            if ($pictureFiles) {
                foreach ($pictureFiles as $pictureFile) {
                    $pictureFileName = $fileLoader->upload($pictureFile);

                    $pictureFile->setFilePath();

                    $travel->addPicture($pictureFile);
                }
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($travel);
            $entityManager->flush();

            return $this->redirectToRoute('admin_travel_index');
        }

        return $this->render('travel/new.html.twig', [
            'travel' => $travel,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="travel_show", methods={"GET"})
     * @param Travel $travel
     * @return Response
     */
    public function show(Travel $travel): Response
    {
        return $this->render('travel/show.html.twig', [
            'travel' => $travel,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="travel_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Travel $travel
     * @return Response
     */
    public function edit(Request $request, Travel $travel): Response
    {
        $form = $this->createForm(TravelType::class, $travel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_travel_index');
        }

        return $this->render('travel/edit.html.twig', [
            'travel' => $travel,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="travel_delete", methods={"DELETE"})
     * @param Request $request
     * @param Travel $travel
     * @return Response
     */
    public function delete(Request $request, Travel $travel): Response
    {
        if ($this->isCsrfTokenValid('delete' . $travel->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($travel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_travel_index');
    }
}
