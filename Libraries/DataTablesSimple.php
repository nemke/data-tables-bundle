<?php

	namespace AppBundle\Libraries;

	use Symfony\Component\Validator\Constraints as Assert;
	use Doctrine\ORM\EntityManager;

	/**
	 * Class that handles JS DataTables backend
	 * 
	 */
	class DataTables
	{
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
		 * Holder for entity manager
		 */
		private $em;

		/**
		 * Class constructor
		 *
		 * @param EntityManager
		 * @return DataTables
		 */
		public function __construct(\Doctrine\ORM\EntityManager $em)
		{
			$this->em = $em;
		}

		/**
		 * Set entity
		 *
		 * @param string $entity
		 * @return DataTables
		 */
		public function setEntity($entity)
		{
		    $this->entity = $entity;

			// Gathering table columns
			$table_columns = $this->em->getClassMetadata($entity)->getFieldNames();
			$this->setTableColumns($table_columns);

		    return $this;
		}

		/**
		 * Set table columns
		 *
		 * @param string $table_columns
		 * @return DataTables
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
		 * @return DataTables
		 */
		public function setAssociationColumns($association_columns)
		{
		    $this->association_columns = $association_columns;

		    return $this;
		}

		/**
		 * Set sorting columns
		 *
		 * @param string $sorting_columns
		 * @return DataTables
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
		 * @return DataTables
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
		 * @return DataTables
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
		 * @return DataTables
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
		 * @return DataTables
		 */
		public function setLength($length)
		{
		    $this->length = $length;

		    return $this;
		}

	    /**
	     * Returnes results from requested table
		 * 
		 * @return array
	     */
	    public function Get()
	    {
	    	$repository = $this->em->getRepository($this->entity);
			$query_builder = $repository->createQueryBuilder('dt');

			$meta_data = $this->em->getClassMetadata($this->entity);
			// Applying search filter
			if (isset($this->search))
			{
				foreach($this->searching_columns as $column)
        			$query_builder
						->orWhere('dt.' . $column . ' LIKE :' . $column)
						->setParameter(':' . $column, $this->search . '%');
			}

			// Applying column sorting
			if (isset($this->sorting_columns))
			{
				foreach($this->sorting_columns as $column)
					$query_builder->addOrderBy('dt.' . $this->table_columns[$column['sorting_column']], $column['sorting_direction']);
			}

			// Setting pagination
			$query_builder->setFirstResult($this->start);
			$query_builder->setMaxResults($this->length);

			// Get items from database
			$query = $query_builder->getQuery();

			$data = $query->getArrayResult();

			// Checking if there is a item
			if (count($data) == 0)
				return FALSE;

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
	    	$repository = $this->em->getRepository($this->entity);
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
	    	$repository = $this->em->getRepository($this->entity);
			$query_builder = $repository->createQueryBuilder('dt');

			$query_builder->select('COUNT(dt)');

			// Applying search filter
			if (isset($this->search))
			{
				foreach($this->searching_columns as $column)
        			$query_builder
						->orWhere('dt.' . $column . ' LIKE :' . $column)
						->setParameter(':' . $column, $this->search . '%');
			}

			// Get items from database
			$query = $query_builder->getQuery();
			return $query->getSingleScalarResult();
	    }
	}