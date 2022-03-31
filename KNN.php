<?php

declare(strict_types=1);

class KNN
{
    private array $similaritiesMatrix;

    public function __construct(
        public Dataset $dataset
    )
    {
        $this->calculateSimilarities();
    }

    private function calculateSimilarities()
    {
        $this->similaritiesMatrix = $this->dataset->cosineSimilarityOfFullMatrix();

    }




}