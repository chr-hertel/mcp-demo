<?php

declare(strict_types=1);

namespace App\Tool;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsTool('list_posts', 'List all posts of the demo blog', 'list')]
#[AsTool('create_post', 'Create a new post for the demo blog', 'create')]
#[AsTool('read_post', 'Read a post of the demo blog', 'read')]
final readonly class PostTool
{
    public function __construct(
        private PostRepository $postRepository,
        private SluggerInterface $slugger,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function list(): string
    {
        return array_reduce($this->postRepository->findAll(),
            static fn (string $carry, Post $post) => $carry.sprintf(' * Title: %s (Slug: %s)', $post->getTitle(), $post->getSlug()).PHP_EOL,
            'Blog Posts:'.PHP_EOL,
        );
    }

    /**
     * @param string $title   Title of the post to create
     * @param string $content Content of the post to create
     * @param string $summary Summary of the post to create
     */
    public function create(string $title, string $content, string $summary): string
    {
        $post = new Post();
        $post->setTitle($title);
        $post->setContent($content);
        $post->setSummary($summary);
        $slug = $this->slugger->slug($post->getTitle())->lower()->toString();
        $post->setSlug($slug);
        $user = $this->userRepository->findOneByUsername('jane_admin');
        $post->setAuthor($user);

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return sprintf('Post created with slug "%s"', $slug);
    }

    /**
     * @param string $slug Slug of post to read
     */
    public function read(string $slug): string
    {
        $post = $this->postRepository->findOneBySlug($slug);

        if ($post === null) {
            throw new \InvalidArgumentException(sprintf('Post with slug "%s" not found', $slug));
        }

        return <<<TEXT
            Title: {$post->getTitle()}
            Slug: {$post->getSlug()}
            
            Content:
            {$post->getContent()}
            TEXT;
    }
}
