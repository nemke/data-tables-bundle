<?php

	namespace Nemke\DataTablesBundle;
	
	use Symfony\Component\HttpKernel\Bundle\Bundle;

	use DataTablesBundle\DependencyInjection\DataTablesExtension;
	
	class DataTablesBundle extends Bundle
	{
	    public function getContainerExtension()
	    {
	        return new DataTablesExtension();
	    }
	}

// END