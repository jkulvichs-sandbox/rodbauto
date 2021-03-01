<?php

namespace Models {

    use ErrorException;
    use Postgres\Postgres;
    use stdClass;

    /**
     * Base model class.
     * All models must inherits from this class.
     */
    abstract class Model
    {
        /**
         * @var Postgres controller
         */
        protected $postgres;

        /**
         * Person constructor.
         * @param Postgres $postgres Postgres controller
         */
        public function __construct($postgres)
        {
            $this->postgres = $postgres;
        }

        /**
         * Name of table for binding
         * @return string
         */
        protected abstract function getTable();

        /**
         * Map for mapping from DB fields to class fields
         * @return array Associative array
         */
        protected abstract function getMap();

        /**
         * Escape string to safety inserting into query
         * @param string $str
         * @return string
         */
        protected function escape($str)
        {
            return pg_escape_string($this->postgres->escape($str));
        }

        /**
         * Return models' list
         * @param Model $modelClass Object of model to clone operations
         * @param string $where Additional args for WHERE statement. **MUST BE ESCAPED**.
         * @param string $limit Limit to how much rows should be selected
         * @param int $offset Offset to from which line Postgre must start to select
         * @return array Array of selected models
         * @throws ErrorException
         */
        public function select($modelClass, $where = "", $limit = "ALL", $offset = 0)
        {
            // construct DB fields to select
            $fieldsSelectors = [];
            foreach ($this->getMap() as $dbField => $pClassField) {
                $fieldsSelectors[] = "$dbField";
            }
            // construct query
            $query = sprintf(
                "SELECT %s FROM %s WHERE %s LIMIT %s OFFSET %s",
                implode(", ", $fieldsSelectors),
                $this->getTable(),
                empty($where) ? "true" : "$where",
                $limit,
                $offset
            );
            // make request
            $rows = $this->postgres->query($query);
            // mapping to objects
            $models = [];
            // for each row
            foreach ($rows as $row) {
                // create new model
                // a little hack to create a new object from existing object constructor
                $model = new $modelClass($this->postgres);
                // write value into each model's fields as described in the model map
                foreach ($model->getMap() as $dbField => &$pClassField) {
                    $pClassField = $row[$dbField];
                }
                // add the model into list
                $models[] = $model;
            }
            return $models;
        }

        /**
         * Select only first record and set their fields to the model object and return self or NULL.
         * @param Model modelClass Object of model to clone operations
         * @param string $where Additional args for WHERE statement. **MUST BE ESCAPED**.
         * @return Model
         * @throws ErrorException
         */
        protected function getBy($modelClass, $where)
        {
            $models = $this->select($modelClass, $where, 1);
            if (count($models) > 1) throw new ErrorException("too many rows found: $where", 1);
            if (count($models) === 0) throw new ErrorException("row not found: $where", 1);

            // set model fields to self fields
            foreach ($models[0] as $field => $value) {
                $this->{$field} = $value;
            }
            return $this;
        }

    }

}
