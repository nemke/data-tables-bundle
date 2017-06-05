<?php

	namespace DataTablesBundle\Libraries;
    use Doctrine\ORM\EntityManager;
    use Symfony\Component\HttpFoundation\Request;

    /**
	 * Post processing abstract class
	 *
	 * @author  Nemanja Andrejevic
	 */
	abstract class PostProcess
	{
        /**
         * @var EntityManager
         */
        protected $entityManager;
        
        /**
         * @var Request
         */
        protected $request;
        
        /**
         * PostProcess constructor.
         *
         * @param EntityManager $entityManager
         * @param Request $request
         */
		public function __construct(EntityManager $entityManager, Request $request)
        {
            $this->entityManager = $entityManager;
            $this->request = $request;
        }

        abstract public function process($data);
	}