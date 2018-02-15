<?php

	namespace Nemke\DataTablesBundle\Libraries;

    /**
     * Class TableOperations
     * @package Nemke\DataTablesBundle\Libraries
     * @author  Nemanja Andrejevic
     */
	class TableOperations
	{
		/**
		 * Builds table alias from table name
		 *
		 * @param $table
		 * @return string
		 */
	    public static function getAlias($table)
	    {
            $alias = '';
			if (mb_strpos($table, '_') !== FALSE)
			{
				$table_name_parts = explode('_', $table);
				foreach ($table_name_parts as $part)
					$alias .= mb_substr($part, 0, 2, 'utf-8');
			}
			else
            {
                $tableWords = preg_split('/(?=[A-Z])/', $table, -1, PREG_SPLIT_NO_EMPTY);

                if (count($tableWords) > 1)
                {
                    foreach ($tableWords as $word)
                        $alias .= mb_substr($word, 0, 2, 'utf-8');
                }
                else
                    $alias = mb_substr($table, 0, 4, 'utf-8');
            }

			return $alias;
	    }
	}