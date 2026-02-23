<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class GbpReview
{
    public readonly int $id;
    public readonly string $reviewId;
    public readonly string $locationName;
    public readonly string $authorName;
    public readonly int $rating;
    public readonly ?string $comment;
    public readonly ?string $reply;
    public readonly ?string $replySuggestion;
    public readonly ?string $sentiment;
    public readonly bool $needsAttention;
    public readonly bool $autoReplied;
    public readonly ?string $publishedAt;
    public readonly ?string $replyPostedAt;

    public function __construct(
        int $id,
        string $reviewId,
        string $locationName,
        string $authorName,
        int $rating,
        ?string $comment,
        ?string $reply,
        ?string $replySuggestion,
        ?string $sentiment,
        bool $needsAttention,
        bool $autoReplied,
        ?string $publishedAt,
        ?string $replyPostedAt,
    ) {
        $this->id = $id;
        $this->reviewId = $reviewId;
        $this->locationName = $locationName;
        $this->authorName = $authorName;
        $this->rating = $rating;
        $this->comment = $comment;
        $this->reply = $reply;
        $this->replySuggestion = $replySuggestion;
        $this->sentiment = $sentiment;
        $this->needsAttention = $needsAttention;
        $this->autoReplied = $autoReplied;
        $this->publishedAt = $publishedAt;
        $this->replyPostedAt = $replyPostedAt;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            reviewId: (string) $data['review_id'],
            locationName: (string) ($data['location_name'] ?? ''),
            authorName: (string) ($data['author_name'] ?? ''),
            rating: (int) ($data['rating'] ?? 0),
            comment: $data['comment'] ?? null,
            reply: $data['reply'] ?? null,
            replySuggestion: $data['reply_suggestion'] ?? null,
            sentiment: $data['sentiment'] ?? null,
            needsAttention: (bool) ($data['needs_attention'] ?? false),
            autoReplied: (bool) ($data['auto_replied'] ?? false),
            publishedAt: $data['published_at'] ?? null,
            replyPostedAt: $data['reply_posted_at'] ?? null,
        );
    }
}
