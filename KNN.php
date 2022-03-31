<?php

declare(strict_types=1);

class KNNMaxHeap extends SplMaxHeap
{
    protected function compare($value1, $value2)
    {
        // First element of tuple is the actual value
        $valueA = $value1[0];
        $valueB = $value2[0];

        $res = parent::compare($valueA, $valueB);

        return $res;
    }
}

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

    /**
     * Returns INDEXES of k-nearest neighbors
     *
     * @param int|string $rowIndex
     * @param $k
     * @return array
     */
    public function kNearestNeighbors(int|string $rowIndex, $k): array
    {
        $heap = new KNNMaxHeap();

        $similaritiesForNode = $this->similaritiesMatrix[$rowIndex];

        foreach ($similaritiesForNode as $key => $similarity) {
            if ($key == $rowIndex) {
                // Skip myself :)

                continue;
            }

            $heap->insert([$similarity, $key]);
        }

        $out = [];

        for ($i = 0; $i < $k; $i++) {
            $el = $heap->extract();

            $out[] = $el[1];
        }

        return $out;

    }




}