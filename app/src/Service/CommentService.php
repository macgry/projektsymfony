<?php

/*
 * This file is part of the YourProject package.
 *
 * (c) Your Name <your-email@example.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Dto\PostListFiltersDto;
use App\Dto\PostListInputFiltersDto;
use App\Entity\Comment;
use App\Entity\Post;
use App\Repository\CommentRepository;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class CommentService.
 */
class CommentService implements CommentServiceInterface
{
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    public function __construct(
        private readonly CategoryServiceInterface $categoryService,
        private readonly PaginatorInterface $paginator,
        private readonly CommentRepository $commentRepository
    ) {}

    /**
     * Get paginated list of all comments (with filters).
     */
    public function getPaginatedList(int $page, PostListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->commentRepository->queryAll($filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get paginated comments for a specific post.
     */
    public function getPaginatedListByPost(Post $post, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->commentRepository->queryAllByPost($post),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    public function save(Comment $comment): void
    {
        $this->commentRepository->save($comment);
    }

    public function delete(Comment $comment): void
    {
        $this->commentRepository->delete($comment);
    }

    private function prepareFilters(PostListInputFiltersDto $filters): PostListFiltersDto
    {
        return new PostListFiltersDto(
            null !== $filters->categoryId
                ? $this->categoryService->findOneById($filters->categoryId)
                : null
        );
    }
}
