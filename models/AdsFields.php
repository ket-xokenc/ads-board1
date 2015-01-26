<?php
use application\core\Model;

class AdsFields extends Model{
    public function selectAllFields($catId){
        return $this->db->query("Select properties.id, properties.name, properties.params, properties.type
                            from categories INNER JOIN property_cats ON categories.id = property_cats.category_id
                            INNER JOIN properties ON properties.id = property_cats.property_id
                            WHERE property_cats.category_id=$catId ORDER BY properties.id");
    }
}