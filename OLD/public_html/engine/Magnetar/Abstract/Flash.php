<?php
	namespace Magnetar;
	
	abstract class Abstract_Flash {
		// flash__process should accept an array of unique ids that need to be pulled
		//    from somewhere and returned with array of 
		abstract protected function flash__process($uniq_ids);
	}