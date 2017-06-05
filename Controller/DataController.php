<?php

	namespace DataTablesBundle\Controller;

	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
	use Symfony\Bundle\FrameworkBundle\Controller\Controller;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\HttpFoundation\JsonResponse;

	use DataTablesBundle\Libraries\DataTablesAdvanced;

	use \Exception as Exception;

	/**
	 * Data controller.
	 *
	 */
	class DataController extends Controller
	{
		const CREATE = 'create';
		const EDIT = 'edit';
		const DELETE = 'delete';

		const COLUMNS = 'columns';
		const SEARCH_COLUMNS = 'search_columns';
		const ASSOCIATIVE_COLUMNS = 'associative_columns';
		const POST_PROCESSING = 'post_process';
		const USER_LIMIT = 'user_limit';

		/**
		 * @Route("/dataTables/{bundle}/{entity}/", name="dataTables")
		 * @Method("POST")
		 * @param $bundle
		 * @param $entity
		 * @return JsonResponse|Response
		 * @throws Exception
		 */
	    public function Data($bundle, $entity)
	    {
	    	// Initializing Data Tables
	    	$em = $this->getDoctrine()->getManager();
			$dataTables = new DataTablesAdvanced($em, $this->get('request_stack')->getCurrentRequest());

			// Loading entities configuration
			$data_tables_entities = $this->getParameter('data_tables.entities');

			// Searching for entity
			foreach ($data_tables_entities as $data_table_entity)
			{
				if ($data_table_entity['name'] == $entity)
				{
					$entity_options = $data_table_entity;
					break;
				}
			}

			// Checking if entity options are found
			if (empty($entity_options))
				throw new Exception('Unknown entity.');

			// Setting entity and search columns
			$dataTables->setTableColumns($entity_options[self::COLUMNS]);
			$dataTables->setSearchingColumns($entity_options[self::SEARCH_COLUMNS]);
			$dataTables->setEntity($bundle . ':' . $entity);
		    $dataTables->setPostProcessing($entity_options[self::POST_PROCESSING]);

			// Setting limits by user
			$userRoles = $this->getUser()->getRoles();
			if (in_array('ROLE_SUPER_ADMIN', array_values($userRoles)) !== FALSE)
				$dataTables->setUserLimit(FALSE);
			else
				$dataTables->setUserLimit($entity_options[self::USER_LIMIT]);

			$dataTables->setUserId($this->getUser()->getId());

			// Pagination part
			if (isset($_POST['start']) && isset($_POST['length']) && ($_POST['length'] != '-1'))
			{
				$dataTables->setStart((int) $_POST['start']);
				$dataTables->setLength((int) $_POST['length']);
			}

			// Search part
			if (isset($_POST['search']['value']) && !empty($_POST['search']['value']))
				$dataTables->setSearch($_POST['search']['value']);

			// Column ordering part
			if (isset($_POST['order']) && ($_POST['order'][0] !== FALSE))
			{
				$sorting_columns = array();

				for($i = 0; $i < count($_POST['order']); $i++)
				{
					$sorting_columns[] =
					array(
						'sorting_column' => $_POST['order'][$i]['column'],
						'sorting_direction' => $_POST['order'][$i]['dir'],
					);
				}

				if(count($sorting_columns) > 0)
					$dataTables->setSortingColumns($sorting_columns);
			}

			// Loading joined tables
			if (!empty($entity_options[self::ASSOCIATIVE_COLUMNS]))
				$dataTables->setAssociationColumns($entity_options[self::ASSOCIATIVE_COLUMNS]);

			// Validating input
			$validator = $this->get('validator');
			$errors = $validator->validate($dataTables);

			if (count($errors) > 0)
				return new Response((string) $errors);

			// Creating response
			$return_array = array();
			$return_array['data'] = $dataTables->Get();
			$return_array['recordsTotal'] = $dataTables->GetCount();
			$return_array['recordsFiltered'] = $dataTables->GetFilteredCount();

			$response = new JsonResponse();
			$response->setData($return_array);

	        return $response;
	    }
	}

// END