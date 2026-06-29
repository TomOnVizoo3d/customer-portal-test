<?php

/**
 * The class representing an organization.
 *
 * @link          https://customers.vizoo3d
 * @since         1.0.0
 *
 * @package       Vizoo_Pipedrive
 * @subpackage    Vizoo_Pipedrive/includes
 */
class Vizoo_Pipedrive_Organization
{
    /**
     * The organization's ids.
     *
     * @since     1.0.0
     * @access    private
     * @var       integer    $id             The organization's pipedrive id.
     * @var       integer    $database_id    The organization's id in the WordPress database.
     */
    public $id;
    public $database_id;

    /**
     * The fields synchronized with pipedrive.
     *
     * @since     1.0.0
     * @access    private
     * @var       string     $name        The organization's name.
     * @var       string     $website     The organization's website.
     * @var       string     $relation    The organization's relation.
     */
    public $name;
    public $website;
    public $relation;

    /**
     * Constructs an organization object.
     *
     * @since     1.0.0
     * @access    private
     * @param     string     $id             The organization's pipedrive id.
     * @param     string     $database_id    The organization's id in the WordPress database.
     * @param     string     $name           The organization's name.
     * @param     string     $relation       The organization's relation.
     * @param     string     $website        Optional. The organization's website.
     */
    public function __construct($id, $database_id, $name, $relation, $website = "")
    {
        $this->id = $id;
        $this->database_id = $database_id;
        $this->name = $name;
        $this->relation = $relation;
        $this->website = $website;
    }
}
