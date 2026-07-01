<?php

/**
 * A container class for activations of LimeLM licenses.
 *
 * @link       https://customers.vizoo3d
 * @since      1.0.0
 *
 *
 * @package    Vizoo_LimeLM
 * @subpackage Vizoo_LimeLM/includes
 */

class Vizoo_LimeLM_Activation
{
    /**
     * The default fields of a LimeLM activation.
     *
     * @since    1.0.0
     * @access   private
     * @var      integer     $id            The activation's id.
     * @var      string      $ip            The ip address where the activation was activated.
     * @var      DateTime    $date          The date when the activation was activated.
     * @var      string      $platform      The platform on which the activation was activated.
     * @var      string      $extra_data    Additional data stored with the activation.
     */
    private $id;
    private $ip;
    private $date;
    private $platform;
    private $extra_data;

    /**
     * Constructs a Vizoo_LimeLM_Activation object.
     *
     * Takes the predefined attributes and constructs a Vizoo_LimeLM_Activation object out
     * of that.
     *
     * The attributes are directly provided by LimeLM and contain all field data stored
     * there.
     *
     * @since    1.0.0
     * @access   private
     * @param    array                $attributes         The license activation's attributes as provided by LimeLM.
     */
    public function __construct($attributes)
    {
        $this->id = $attributes['id'];
        $this->ip = $attributes['ip'];
        $this->date = new DateTime($attributes['date']);
        $this->platform = $attributes['type'];
        $this->extra_data = array_key_exists('extra_data', $attributes) ? $attributes['extra_data'] : '';
    }


    public function get_id()
    {
        return $this->id;
    }

    public function get_ip()
    {
        return $this->ip;
    }

    public function get_date()
    {
        return $this->date;
    }

    public function get_formatted_date($format = 'd-M-Y')
    {
        return $this->date->format($format);
    }

    public function get_platform()
    {
        return $this->platform;
    }

    public function get_platform_title()
    {
        return $this->platform;
    }

    public function is_deactivatable()
    {
        return $this->extra_data === 'online';
    }
}
