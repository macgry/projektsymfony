<?php

/**
 * Post controller.
 */

namespace App\Controller;

use App\Dto\PostListInputFiltersDto;
use App\Entity\Post;
use App\Entity\User;
use App\Form\Type\PostType;
use App\Resolver\PostListInputFiltersDtoResolver;
use App\Service\PostServiceInterface;
use App\Service\CommentServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class PostController.
 */
#[Route('/post')]
class PostController extends AbstractController
{
    private readonly PostServiceInterface $postService;
    private readonly TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param PostServiceInterface $postService Post service
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(PostServiceInterface $postService, TranslatorInterface $translator)
    {
        $this->postService = $postService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param PostListInputFiltersDto $filters Input filters
     * @param int                     $page    Page number
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'post_index',
        methods: 'GET'
    )]
    public function index(#[MapQueryString(resolver: PostListInputFiltersDtoResolver::class)] PostListInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $pagination = $this->postService->getPaginatedList(
            $page,
            $user,
            $filters
        );

        return $this->render('post/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Post                    $post           Post entity
     * @param Request                 $request        HTTP request
     * @param CommentServiceInterface $commentService Comment service
     *
     * @return Response HTTP response
     */
    #[Route('/post/{id}', name: 'post_show', methods: ['GET'])]
    public function show(Post $post, Request $request, CommentServiceInterface $commentService): Response
    {
        $page = $request->query->getInt('page', 1);
        $commentPagination = $commentService->getPaginatedListByPost($post, $page);

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'commentPagination' => $commentPagination,
        ]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/create', name: 'post_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $post = new Post();
        $post->setAuthor($user);
        $form = $this->createForm(
            PostType::class,
            $post,
            ['action' => $this->generateUrl('post_create')]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->save($post);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'post/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Post    $post    Post entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'post_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(
            PostType::class,
            $post,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('post_edit', ['id' => $post->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->save($post);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'post/edit.html.twig',
            [
                'form' => $form->createView(),
                'post' => $post,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Post    $post    Post entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'post_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Post $post): Response
    {
        $form = $this->createForm(
            FormType::class,
            $post,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('post_delete', ['id' => $post->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$post->getComments()->isEmpty()) {
                $this->addFlash('error', $this->translator->trans('message.delete_blocked_comments'));

                return $this->redirectToRoute('post_index');
            }

            $this->postService->delete($post);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'post/delete.html.twig',
            [
                'form' => $form->createView(),
                'post' => $post,
            ]
        );
    }
}
