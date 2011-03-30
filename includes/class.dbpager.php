<?PHP
    class DBPager extends Pager
    {
        private $itemClass;
        private $countSql;
        private $pageSql;

        public function __construct($itemClass, $countSql, $pageSql, $page, $per_page)
        {
            $this->itemClass = $itemClass;
            $this->countSql  = $countSql;
            $this->pageSql   = $pageSql;

            $db = Database::getDatabase();
            $num_records = intval($db->getValue($countSql));

            parent::__construct($page, $per_page, $num_records);
        }

        public function calculate()
        {
            parent::calculate();
            // load records .. see $this->firstRecord, $this->perPage
            $limitSql = sprintf(' LIMIT %s,%s', $this->firstRecord, $this->perPage);
            $this->records = array_values(DBObject::glob($this->itemClass, $this->pageSql . $limitSql));
        }
    }
