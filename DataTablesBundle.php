<?php

	namespace Nemke\DataTablesBundle;
	
	use Symfony\Component\HttpKernel\Bundle\Bundle;

	use Nemke\DataTablesBundle\DependencyInjection\DataTablesExtension;
	
	class DataTablesBundle extends Bundle
	{
	    public function getContainerExtension()
	    {
	        return new DataTablesExtension();
	    }
	}

// END