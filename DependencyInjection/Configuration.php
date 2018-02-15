<?php

	namespace Nemke\DataTablesBundle\DependencyInjection;

	use Symfony\Component\Config\Definition\Builder\TreeBuilder;
	use Symfony\Component\Config\Definition\ConfigurationInterface;

	/**
	 * This class contains the configuration information for the bundle
	 *
	 * This class how different entities are handled
	 *
	 * @author Andrejevic Nemanja
	 */
	class Configuration implements ConfigurationInterface
	{
	    /**
	     * Generates the configuration tree.
	     *
	     * @return TreeBuilder
	     */
	    public function getConfigTreeBuilder()
	    {
	        $treeBuilder = new TreeBuilder();
	        $rootNode = $treeBuilder->root('data_tables');

	        $rootNode
	            ->children()
	                ->arrayNode('entities')
						->prototype('array')
		                    ->children()
								->scalarNode('name')
									->cannotBeEmpty()
								->end()
								->arrayNode('columns')
									->prototype('scalar')
										->cannotBeEmpty()
									->end()
                                    ->defaultValue([])
								->end()
								->arrayNode('search_columns')
									->prototype('scalar')
										->cannotBeEmpty()
									->end()
								->end()
								->scalarNode('post_process')
                                    ->defaultValue('')
								->end()
								->scalarNode('user_limit')
									->defaultValue(0)
								->end()
								->arrayNode('associative_columns')
									->prototype('array')
										->children()
											->scalarNode('field')
												->cannotBeEmpty()
											->end()
											->scalarNode('table')
												->cannotBeEmpty()
											->end()
											->scalarNode('target_field')
												->cannotBeEmpty()
											->end()
											->arrayNode('target_fields')
												->prototype('scalar')
													->cannotBeEmpty()
												->end()
											->end()
											->scalarNode('join_type')
											->end()
										->end()
									->end()
								->end()
		                    ->end()
						->end()
	                ->end()
	            ->end();

	        return $treeBuilder;
	    }
	}

// END