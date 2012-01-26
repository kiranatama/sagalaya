<?php

namespace app\extensions\helper;

use lithium\template\Helper;
use app\extensions\util\DomBuilder;

/**
 * Render calendar using table
 *
 * @author Mukhamad Ikhsan
 */
class Calendar extends Helper {
				
	/*
	 * Number of month displayed
	 */
	protected $displayedMonth = 1;

	/*
	 * Style for rendering calendar
	 */
	protected $style = array(
				"div" => "calendar", "table" => "calendar", 
				"title" => "month", "current" => "current");

	/*
	 * Is day writes as abbreviate
	 */
	protected $abbreviate = false;	
 	
	/**
	 * build config for calendar
	 * @param array $options
	 */
 	public function buildConfig($options = array()) {
 		foreach ($options as $option => $value) {
 			$this->$option = $value;
 		}
 	} 
 	
 	/**
 	 * render calendar called in view via helper
 	 * @param mixed $date
 	 * @param Array $options
 	 */
 	public function render($date = null, $options = array()) {
 		 		 		 		 	
 		$calendar = $this->parsed($date); 		 		
 		$this->buildConfig($options);
 		$container = new DomBuilder('div', array('id' => $this->style['div'])); 		
 		 		
 		for ($month = 0; $month < $this->displayedMonth; $month++) {			 
 						 			
 			$monthDays = cal_days_in_month(CAL_GREGORIAN, 
 								$calendar->format('n'), $calendar->format('Y'));
 			 			 		
 			if ($calendar->format('j') != 1) {
 				$calendar->sub(new \DateInterval("P{$calendar->format('j')}D"))
 							->add(new \DateInterval("P1D"));
 			}
 			 			 			
 			$firstDay = $calendar->format('N');
 			$weeks = ceil(($monthDays + $firstDay) / 7);
 			
 			$table = new DomBuilder('table', array('class' => $this->style['table'])); 			
 			$th = $table->addChild('thead')
 				  		->addChild('tr', array('class' => $this->style['title']))
 				  		->addChild('th', array('colspan' => 7, 
 				  							   'class' => $this->style['title']));
 			
 			$th->content = $calendar->format('F Y'); 	
 			$days = $table->get('thead')->addChild('tr');
 			
 			for ($day = 0; $day < 7; $day++) {
 				$cell = $days->addChild('th');
 				$cell->content = ($this->abbreviate) ? jddayofweek($day, 2) : jddayofweek($day, 1);
 			}
 			 	  			 		
 			for ($week = 0; $week < $weeks; $week++) {
 				$weekdate = $table->addChild('tr');	
 				for ($day = 0; $day < 7; $day++) {
 					$cellNumber = $week * 7 + $day;
 					$class = ($calendar->format('d-m-Y') == date('d-m-Y')) ? $this->style['current'] : null;
 					$cell = $weekdate->addChild('td', array('class' => $class));
 					if ($cellNumber + 1 >= $firstDay && $cellNumber + 1 < $monthDays + $firstDay) {
 						$cell->content = $this->renderCell($calendar);
 						$calendar->add(new \DateInterval("P1D"));
 					}
 				}
 			}
 			 			 			
 			$container->addChild($table); 			
 		}
 		 		
 		return $container->render();
 	}
 	
 	/**
 	 * render day date in <td> tag
 	 * @param DateTime $date
 	 */
 	public function renderCell($date) { 		
 		return $date->format('j');
 	} 
 	
	/**
	 * Parse string date to DateTime Object
	 * @param mixed $date 
	 * 		null 	: will initialized by current date
	 * 		array 	: [date] => '2010/08/2', [format] => 'Y/m/d' 	 
 	 * 		string	: '2010/08/2' OR '2010-08-2' ('Y/m/d')
 	 * @return \DateTime $instance
	 */
 	public function parsed($date = null) {
 		
 		$instance = new \DateTime();
 		
 		if (isset($date)) {
 			if (is_a($date, get_class(new \DateTime))) {
 				$instance = $date;
 			} else if (is_array($date)) { 				 				
 				$instance = \DateTime::createFromFormat($date['format'], $date['date']);
 			} else {
 				$instance = new \DateTime($date);
 			}
 		}
 		
 		return $instance;
 	}	
}

?>