<?php

	namespace Nemke\DataTablesBundle\DependencyInjection;

	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\HttpKernel\DependencyInjection\Extension;

	/**
	 * DataTables Extension
	 *
	 */
	class DataTablesExtension extends Extension
	{
		public function load(array $configs, ContainerBuilder $container)
		{
			// The next 2 lines are pretty common to all Extension templates.
			$configuration = new Configuration();
			$processedConfig = $this->processConfiguration($configuration, $configs);

			$container->setParameter('data_tables.entities', $processedConfig['entities']);
	    }
	}

// END