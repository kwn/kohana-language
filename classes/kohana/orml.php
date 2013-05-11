<?php defined('SYSPATH') or die('No direct script access.');


class Kohana_ORML extends Kohana_ORM {

    
    /**
	 * Constructs a new model and loads a record if given
	 *
	 * @param   mixed $id Parameter for find or object to load
	 * @return  void
	 */
	public function __construct($id = NULL)
	{
		$this->_initialize();

        $this->_has_one = array_merge($this->_has_one, array(
            'translation' => array('model' => $this->_object_name.'_lang', 'foreign_key' => $this->_object_name.'_id'),
        ));

        $this->_load_with = array_merge($this->_load_with, array(
            'translation'
        ));

		if ($id !== NULL)
		{
			if (is_array($id))
			{
				foreach ($id as $column => $value)
				{
					// Passing an array of column => values
					$this->where($column, '=', $value);
				}

				$this->find();
			}
			else
			{
				// Passing the primary key
				$this->where($this->_object_name.'.'.$this->_primary_key, '=', $id)->find();
			}
		}
		elseif ( ! empty($this->_cast_data))
		{
			// Load preloaded data from a database call cast
			$this->_load_values($this->_cast_data);

			$this->_cast_data = array();
		}
	}


    /**
	 * Binds another one-to-one object to this model.  One-to-one objects
	 * can be nested using 'object1:object2' syntax
	 *
	 * @param  string $target_path Target model to bind to
	 * @return void
	 */
	public function with($target_path)
	{
		if (isset($this->_with_applied[$target_path]))
		{
			// Don't join anything already joined
			return $this;
		}

		// Split object parts
		$aliases = explode(':', $target_path);
		$target = $this;
		foreach ($aliases as $alias)
		{
			// Go down the line of objects to find the given target
			$parent = $target;
			$target = $parent->_related($alias);

			if ( ! $target)
			{
				// Can't find related object
				return $this;
			}
		}

		// Target alias is at the end
		$target_alias = $alias;

		// Pop-off top alias to get the parent path (user:photo:tag becomes user:photo - the parent table prefix)
		array_pop($aliases);
		$parent_path = implode(':', $aliases);

		if (empty($parent_path))
		{
			// Use this table name itself for the parent path
			$parent_path = $this->_object_name;
		}
		else
		{
			if ( ! isset($this->_with_applied[$parent_path]))
			{
				// If the parent path hasn't been joined yet, do it first (otherwise LEFT JOINs fail)
				$this->with($parent_path);
			}
		}

		// Add to with_applied to prevent duplicate joins
		$this->_with_applied[$target_path] = TRUE;

		// Use the keys of the empty object to determine the columns
		foreach (array_keys($target->_object) as $column)
		{
			$name = $target_path.'.'.$column;
			$alias = $target_path.':'.$column;

			// Add the prefix so that load_result can determine the relationship
			$this->select(array($name, $alias));
		}

		if (isset($parent->_belongs_to[$target_alias]))
		{
			// Parent belongs_to target, use target's primary key and parent's foreign key
			$join_col1 = $target_path.'.'.$target->_primary_key;
			$join_col2 = $parent_path.'.'.$parent->_belongs_to[$target_alias]['foreign_key'];
		}
		else
		{
			// Parent has_one target, use parent's primary key as target's foreign key
			$join_col1 = $parent_path.'.'.$parent->_primary_key;
			$join_col2 = $target_path.'.'.$parent->_has_one[$target_alias]['foreign_key'];
		}

        if ($target_path === 'translation')
        {
            $union_alias = substr($this->_object_name, 0, 1).'d';
            $union_langs = substr($this->_object_name, 0, 1).'l';

            $union_notin = DB::select($this->_object_name.'_id')
                ->from(array($this->_object_name.'_langs', $union_langs))
                ->where($union_langs.'.language_id', '=', Language::current()->id);

            $union = DB::select()
                ->from(array($this->_object_name.'_langs', $union_alias))
                ->where($union_alias.'.language_id', '=', Language::base()->id)
                ->and_where($union_alias.'.'.$this->_object_name.'_id', 'NOT IN', $union_notin);

            $query = DB::select()
                ->from(array($this->_object_name.'_langs', $union_langs))
                ->where($union_langs.'.language_id', '=', Language::current()->id)
                ->union($union);

            $this->join(array($query, $target_path), 'LEFT')->on($join_col1, '=', $join_col2);
        }
        else {
            // Join the related object into the result
            $this->join(array($target->_table_name, $target_path), 'LEFT')->on($join_col1, '=', $join_col2);
        }

		return $this;
	}

}