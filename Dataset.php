<?php

class Dataset implements ArrayAccess
{
    private array $columns;
    private array $indexes;

    private array $data;

    public function __construct($data)
    {
        $columns = $data[0];

        $i = 0;

        foreach ($columns as $column) {
            $this->columns[$column] = $i;

            $i++;
        }

        // Remove first row which is usually column names
        unset($data[0]);

        $this->data = array_values($data);
    }

    public function offsetExists($offset)
    {
        return isset($this->columns[$offset]);
    }

    public function offsetGet($offset): Dataset|array
    {
        if (is_string($offset)) {
            if (!$this->offsetExists($offset)) {
                return new Dataset([]);
            }

            $index = $this->columns[$offset];

            $data = [];

            foreach ($this->data as $row) {
                $data[] = [$index => $row[$index]];
            }

            array_unshift($data, [$index => $offset]);

            return new Dataset($data);
        }

        if (is_int($offset)) {
            return $this->data[$offset];
        }

    }

    public function offsetSet($offset, $value)
    {
        throw new Exception(__METHOD__ . ' not implemented');
    }

    public function offsetUnset($offset)
    {
        throw new Exception(__METHOD__ . ' not implemented');
    }

    public function columns()
    {
        return array_flip($this->columns);
    }

    public function range($from = 0, $to = null)
    {
        $out = [];

        return array_slice($this->data, $from, $to);
    }

    public function pivot(string $index, string $column, string $value)
    {
        $columns = [];
        $indexes = [];

        $ix = $this->columns[$index];
        $col = $this->columns[$column];
        $val = $this->columns[$value];

        $data = [];

        // Go once through data and collect all cols and indexes that exist
        foreach ($this->data as $row) {
            $index = $row[$ix];
            $column = $row[$col];

            if (!array_key_exists($index, $indexes)) {
                $indexes[$index] = True;
            }

            if (!array_key_exists($column, $columns)) {
                $columns[$column] = True;
            }
        }

        // Now that we have unique indexes and columns - let's create a matrix

        $data = [];

        foreach ($indexes as $index => $foo) {
            $data[$index] = [];

            foreach ($columns as $column => $bar) {
                $data[$index][$column] = null;
            }
        }

        // Finally, iterate through actual data and get a pivoted version
        foreach ($this->data as $row) {
            $index = $row[$ix];
            $column = $row[$col];
            $value = $row[$val];

            $data[$index][$column] = $value;
        }

        // Finally finally :D Reset array indexes!
        foreach ($data as $row => $cols) {
            $data[$row] = array_values($cols);
        }

        // Plug in the column names on top

        array_unshift($data, array_keys($columns));

        $dataset = new Dataset($data);

        return $dataset;
    }

    public function cosineSimilarityByNumericalIndex(int $indexA, int $indexB): float
    {
        $vectorA = $this->data[$indexA];
        $vectorB = $this->data[$indexB];

        return $this->_cosineSimilarity($vectorA, $vectorB);
    }

    private function _cosineSimilarity(array $vectorA, array $vectorB): float
    {
        assert(count($vectorA) == count($vectorB), "Vectors should be of same size");

        $product = 0;
        $vectorASum = 0;
        $vectorBSum = 0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $product += $vectorA[$i] * $vectorB[$i];
            $vectorASum += pow($vectorA[$i], 2);
            $vectorBSum += pow($vectorB[$i], 2);
        }

        $out = $product / (sqrt($vectorASum) * sqrt($vectorBSum));

        return $out;
    }

    public function cosineSimilarity(string $indexA, string $indexB)
    {
        $vectorA = $this->data[$indexA];
        $vectorB = $this->data[$indexB];

        return $this->_cosineSimilarity($vectorA, $vectorB);
    }

}