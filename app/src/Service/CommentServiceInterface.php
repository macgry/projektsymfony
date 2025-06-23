<?php

/**
 * Comment service interface.
 */

namespace App\Service;

use App\Dto\PostListInputFiltersDto;
use App\Entity\Comment;
use App\Entity\Post;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface CommentServiceInterface.
 */
interface CommentServiceInterface
{
    /**
     * Get paginated list of all comments (filtered).
     *
     * @param int                     $page    Page number
     * @param PostListInputFiltersDto $filters Filters for querying comments
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, PostListInputFiltersDto $filters): PaginationInterface;

    /**
     * Get paginated list of comments for a specific post.
     *
     * @param Post $post Post entity
     * @param int  $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedListByPost(Post $post, int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Comment $comment Comment entity
     */
    public function save(Comment $comment): void;

    /**
     * Delete entity.
     *
     * @param Comment $comment Comment entity
     */
    public function delete(Comment $comment): void;
}
