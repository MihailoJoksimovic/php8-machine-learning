<?php


class Formatter
{
    public function printData(Dataset $dataset, array $data)
    {
        $columns = $dataset->columns();

        $colLenghts = [];

        foreach ($data as $row) {
            $i = 0;

            foreach ($row as $val) {
                if (!isset($colLenghts[$i])) {
                    $colLenghts[$i] = 0;
                }

                if (strlen($val) > $colLenghts[$i]) {
                    $colLenghts[$i] = strlen($val);
                }

                $i++;
            }
        }

        foreach ($colLenghts as $key => $colLength) {
            $colLenghts[$key] += 6;
        }

        $out = "";

        foreach ($columns as $key => $column) {
            $colLength = $colLenghts[$key];

            $out .= "|   " . $column . str_repeat(" ", ($colLength - strlen($column)) + 3);
        }

        $out .= "|\n";

        foreach ($data as $row) {
            foreach ($row as $key => $val) {
                $colLength = $colLenghts[$key];
                $out .= "|   " . $val . str_repeat(" ", ($colLength - strlen($val)) + 3);
            }

            $out .= "|\n";
        }

        echo $out;
    }
}