<?php

	namespace Nemke\DataTablesBundle\Libraries;

    use Doctrine\Common\Persistence\ObjectManager;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Validator\Constraints as Assert;
	use Doctrine\ORM\EntityManager;

	/**
	 * Class that handles JS DataTables backend
	 *
	 * @author  Nemanja Andrejevic
	 */
	class DataProcessor
	{
		const FIELD = 'field';
		const TABLE = 'table';
		const TARGET_FIELD = 'target_field';
		const TARGET_FIELDS = 'target_fields';
		const JOIN_TYPE = 'join_type';
		const LEFT_JOIN = 'left';

		/**
		 * @Assert\Type(type="integer", message="The value {{ value }} is not a valid {{ type }}.")
		 */
		private $start = 0;

		/**
		 * @Assert\Type(type="integer", message="The value {{ value }} is not a valid {{ type }}.")
		 */
		private $length = 10;

		/**
		 * @Assert\Type(type="string", message="The value {{ value }} is not a valid {{ type }}.")
		 */
		private $search;

		/**
		 * @Assert\Type(type="array", message="The value {{ value }} is not a valid {{ type }}.")
		 */
		private $sorting_columns;

		/**
		 * @Assert\Type(type="string", message="The value {{ value }} is not a valid {{ type }}.")
		 */
		private $entity;

		/**
		 * Table name
		 */
		private $table;

		/**
		 * @Assert\Type(type="array", message="The value {{ value }} is not a valid {{ type }}.")
		 */
		private $table_columns;

		/**
		 * @Assert\Type(type="array", message="The value {{ value }} is not a valid {{ type }}.")
		 */
		private $association_columns;

		/**
		 * @Assert\Type(type="array", message="The value {{ value }} is not a valid {{ type }}.")
		 */
		private $searching_columns;

        /**
         * @Assert\Type(type="string", message="The value {{ value }} is not a valid {{ type }}.")
         */
        private $postProcessing;

		/**
		 * Holder for entity manager
		 */
		private $entityManager;
        
        /**
         * @var Request
         */
        private $request;
        
		/**
		 * Determines if query will be limited with current user id
		 */
		private $userLimit;


        /**
         * Holder for user id
         */
        private $userId;

        /**
         * DataProcessor constructor.
         * @param ObjectManager $entityManager
         * @param Request $request
         */
        public function __construct(ObjectManager $entityManager, Request $request)
        {
            $this->entityManager = $entityManager;
            $this->request = $request;
        }

		/**
		 * @return mixed
		 */
		public function getUserLimit()
		{
			return $this->userLimit;
		}

		/**
		 * @param mixed $userLimit
		 * @return DataProcessor
		 */
		public function setUserLimit($userLimit)
		{
			$this->userLimit = $userLimit;
			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getUserId()
		{
			return $this->userId;
		}

		/**
		 * @param mixed $userId
		 * @return DataProcessor
		 */
		public function setUserId($userId)
		{
			$this->userId = $userId;
			return $this;
		}
        
        /**
         * Set entity
         *
         * @param string $entity
         * @return DataProcessor
         */
		public function setEntity($entity)
		{
		    $this->entity = $entity;

			// Gathering table columns
			$this->table = $this->entityManager->getClassMetadata($entity)->getTableName();

		    return $this;
		}

        /**
         * Set table columns
         *
         * @param string $table_columns
         * @return DataProcessor
         */
		public function setTableColumns($table_columns)
		{
		    $this->table_columns = $table_columns;

		    return $this;
		}

        /**
         * Set association columns
         *
         * @param string $association_columns
         * @return DataProcessor
         */
		public function setAssociationColumns($association_columns)
		{
		    $this->association_columns = $association_columns;

		    return $this;
		}

        /**
         * Set sorting columns
         *
         * @param array $sorting_columns
         * @return DataProcessor
         */
		public function setSortingColumns($sorting_columns)
		{
		    $this->sorting_columns = $sorting_columns;

		    return $this;
		}

        /**
         * Set searching columns
         *
         * @param string $searching_columns
         * @return DataProcessor
         */
		public function setSearchingColumns($searching_columns)
		{
		    $this->searching_columns = $searching_columns;

		    return $this;
		}

		/**
		 * Set search
		 *
		 * @param string $search
         * @return DataProcessor
		 */
		public function setSearch($search)
		{
		    $this->search = $search;

		    return $this;
		}

        /**
         * Set start
         *
         * @param string $start
         * @return DataProcessor
         */
		public function setStart($start)
		{
		    $this->start = $start;

		    return $this;
		}

        /**
         * Set length
         *
         * @param string $length
         * @return DataProcessor
         */
		public function setLength($length)
		{
		    $this->length = $length;

		    return $this;
		}

        /**
         * Set post processing
         *
         * @param $postProcessing
         * @return DataProcessor
         */
		public function setPostProcessing($postProcessing)
		{
			$this->postProcessing = $postProcessing;

			return $this;
		}

	    /**
	     * Return results from requested table
		 *
		 * @return array|bool
	     */
	    public function Get()
	    {
	    	$association_column_aliases = array();
	    	$connection = $this->entityManager->getConnection();
			$query_builder = $connection->createQueryBuilder();

			$alias = TableOperations::getAlias($this->table);
            $query_builder
            	->select($alias . '.*')
            	->from($this->table, $alias);

			// Building associations
			if (isset($this->association_columns))
			{
				foreach ($this->association_columns as $association)
				{
					$join_alias = TableOperations::getAlias($association[self::TABLE]);

					// Building select for every association
					foreach ($association[self::TARGET_FIELDS] as $field)
					{
						$query_builder->addSelect($join_alias . '.' . $field . ' AS ' . $join_alias . '_' . $field);
						$association_column_aliases [] = $join_alias . '_' . $field;
					}

					// Adding join for every association
					if (isset($association[self::JOIN_TYPE]) && $association[self::JOIN_TYPE] == self::LEFT_JOIN)
						$query_builder->leftJoin($alias, $association[self::TABLE], $join_alias, $join_alias . '.' . $association[self::TARGET_FIELD] . ' = ' . $alias . '.' . $association[self::FIELD]);
					else
						$query_builder->innerJoin($alias, $association[self::TABLE], $join_alias, $join_alias . '.' . $association[self::TARGET_FIELD] . ' = ' . $alias . '.' . $association[self::FIELD]);
				}
			}

			// Applying search filter
			if (isset($this->search))
			{
				foreach($this->searching_columns as $column)
        			$query_builder
						->orWhere($alias . '.' . $column . ' LIKE :' . $column)
						->setParameter(':' . $column, $this->search . '%');
			}

			// Applying column sorting
			if (isset($this->sorting_columns))
			{
				foreach($this->sorting_columns as $column)
					$query_builder->addOrderBy($alias . '.' . $this->table_columns[$column['sorting_column']], $column['sorting_direction']);
			}

			// Limiting query with user ID
			if ($this->userLimit)
				$query_builder->andWhere($alias . '.user_id = ' . $this->userId);

			// Setting pagination
			$query_builder->setFirstResult($this->start);
			$query_builder->setMaxResults($this->length);

			// Get items from database
			$query = $query_builder->execute();

			$data = $query->fetchAll();

			// Checking if there is a item
			if (count($data) == 0)
				return FALSE;

			// Inserting association columns as attributes on row level
			if (!empty($association_column_aliases))
			{
				foreach ($data as $key => $row)
				{
					$data[$key]['DT_RowAttr'] = array();

					foreach ($association_column_aliases as $variable)
						$data[$key]['DT_RowAttr']['data-' . $variable] = $data[$key][$variable];
				}
			}

			// Inserting association columns as attributes on row level
			foreach ($data as $key => $row)
			{
				$data[$key]['DT_RowAttr'] = array();

				foreach ($row as $columnName => $columnValue)
					$data[$key]['DT_RowAttr']['data-' . $columnName] = $columnValue;
			}

            /*
             * Post processing
             */
            if (!empty($this->postProcessing))
            {
                $postProcessingClass = new $this->postProcessing($this->entityManager, $this->request);
				/** @noinspection PhpUndefinedMethodInspection */
				$data = $postProcessingClass->process($data);
            }

			return $data;
	    }

	    /**
	     * Returns number of total rows
		 *
		 * @return string
	     */
	    public function GetCount()
	    {
	    	// Counting all rows
	    	$repository = $this->entityManager->getRepository($this->entity);
			$query_builder = $repository->createQueryBuilder('dt');
			$query_builder->select('COUNT(dt)');

			// Get items from database
			$query = $query_builder->getQuery();
			return $query->getSingleScalarResult();
	    }

	    /**
	     * Returns number of filtered rows
		 *
		 * @return string
	     */
	    public function GetFilteredCount()
	    {
	    	$association_column_aliases = array();
	    	$connection = $this->entityManager->getConnection();
			$query_builder = $connection->createQueryBuilder();

			$alias = TableOperations::getAlias($this->table);
            $query_builder
            	->select('count(*)')
            	->from($this->table, $alias);

			// Building associations
			if (isset($this->association_columns))
			{
				foreach ($this->association_columns as $association)
				{
					$join_alias = TableOperations::getAlias($association[self::TABLE]);

					// Building select for every association
					foreach ($association[self::TARGET_FIELDS] as $field)
					{
						$query_builder->addSelect($join_alias . '.' . $field . ' AS ' . $join_alias . '_' . $field);
						$association_column_aliases [] = $join_alias . '_' . $field;
					}

					// Adding join for every association
					if (isset($association[self::JOIN_TYPE]) && $association[self::JOIN_TYPE] == self::LEFT_JOIN)
						$query_builder->leftJoin($alias, $association[self::TABLE], $join_alias, $join_alias . '.' . $association[self::TARGET_FIELD] . ' = ' . $alias . '.' . $association[self::FIELD]);
					else
						$query_builder->innerJoin($alias, $association[self::TABLE], $join_alias, $join_alias . '.' . $association[self::TARGET_FIELD] . ' = ' . $alias . '.' . $association[self::FIELD]);
				}
			}

			// Applying search filter
			if (isset($this->search))
			{
				foreach($this->searching_columns as $column)
        			$query_builder
						->orWhere($alias . '.' . $column . ' LIKE :' . $column)
						->setParameter(':' . $column, $this->search . '%');
			}

			// Limiting query with user ID
			if ($this->userLimit)
				$query_builder->andWhere($alias . '.user_id = ' . $this->userId);

			// Get items from database
			$query = $query_builder->execute();

			return $query->fetchColumn();
	    }
	}