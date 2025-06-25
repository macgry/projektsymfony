<?php

/**
 * Comment repository.
 */

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    /**
     * CommentRepository constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Query all comments by post.
     *
     * @param Post $post Post entity
     *
     * @return QueryBuilder QueryBuilder
     */
    public function queryAllByPost(Post $post): QueryBuilder
    {
        return $this->createQueryBuilder('comment')
            ->select('partial comment.{id, nick, email, content}')
            ->andWhere('comment.post = :post')
            ->setParameter('post', $post)
            ->orderBy('comment.id', 'DESC');
    }

    /**
     * Save entity.
     *
     * @param Comment $comment Comment entity
     *
     * @throws ORMException
     */
    public function save(Comment $comment): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($comment);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Comment $comment Comment entity
     *
     * @throws ORMException
     */
    public function delete(Comment $comment): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($comment);
        $this->_em->flush();
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder QueryBuilder
     *
     * @return QueryBuilder QueryBuilder
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('comment');
    }
}
