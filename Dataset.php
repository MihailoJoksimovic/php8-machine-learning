<?php

class Dataset implements ArrayAccess, Countable
{
    private array $columns;

    /**
     * Columns that are numerical. This is used for all operations that require
     * calculations.
     *
     * @var array
     */
    private array $numericalColumns;

    private array $indexes;

    private array $data;

    private ?string $idColumn;

    private ?int $idColumnIndex;

    public function __construct($data, $idColumn = null)
    {
        $columns = $data[0];

        $i = 0;

        $idColumnFound = false;

        $this->columns = [];

        foreach ($columns as $column) {
            $this->columns[$column] = $i;

            if ($idColumn && $column == $idColumn) {
                $idColumnFound = true;

            }

            $i++;
        }

        if ($idColumn && !$idColumnFound) {
            throw new \InvalidArgumentException("ID Column '$idColumn' not found in column list!");
        }

        if ($idColumn) {
            $this->idColumn = $idColumn;

            $this->idColumnIndex = $this->columns[$idColumn];

            unset($this->columns[$idColumn]);
        }

        // Remove first row which is usually column names
        unset($data[0]);

        if (!$idColumn) {
            $this->data = array_values($data);
        } else {
            $idColumnIndex = $this->idColumnIndex;

            $this->data = [];

            foreach ($data as $row) {
                $rowId = $row[$idColumnIndex];

                unset($row[$idColumnIndex]);

                $this->data[$rowId] = $row;
            }
        }

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

    public function count()
    {
        return count($this->data);
    }

    public function columns()
    {
        return array_flip($this->columns);
    }

    public function dropColumn(string $columnName)
    {
        $columnIndex = $this->columns[$columnName];

        $newColumns = $this->columns;

        unset($newColumns[$columnName]);

        $newData = [];

        foreach ($this->data as $row) {
            unset($row[$columnIndex]);

            $newData[] = $row;
        }

        array_unshift($newData, $newColumns);

        return new Dataset($newData);
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

        $columns = $this->columns;

        foreach ($columns as $column) {
            if (!is_numeric($vectorA[$column]) || !is_numeric($vectorB[$column])) {
                // Skip non-numerical columns. Ideally to be replaced with EXPLICIT way of saying
                // which columns are numeric!

                continue;
            }

            $product += $vectorA[$column] * $vectorB[$column];
            $vectorASum += pow($vectorA[$column], 2);
            $vectorBSum += pow($vectorB[$column], 2);
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

    /**
     * Returns n x n matrix with cosine similarity coefficients between
     * each row
     */
    public function cosineSimilarityOfFullMatrix()
    {
        $out = [];

        $numEntries = count($this->data);

        echo "Num entries in matrix: $numEntries " . PHP_EOL;

        $indexes = array_keys($this->data);

        foreach ($indexes as $indexA) {
            echo "Calculating similarities for Index: $indexA " . PHP_EOL;

            $similarities = [];

            foreach ($indexes as $indexB) {
                $similarities[] = $this->cosineSimilarityByNumericalIndex($indexA, $indexB);
            }

            $out[] = $similarities;
        }

        return $out;
    }

    public function head(int $n)
    {
        $columns = $this->columns;

        $data = [];

        $data = array_slice($this->data, 0, $n);

        array_unshift($data, $columns);

        return new Dataset($data);
    }

    public function fillna(int|float $value): void
    {
        throw new \Exception(__METHOD__ . " not implemented");
    }
}