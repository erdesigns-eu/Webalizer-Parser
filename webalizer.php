<?php

/**********************************************************************************/
/*										  */
/*				webalizer.php 					  */
/*										  */
/*				Author	: Ernst Reidinga 			  */
/*				Date 	: 27/12/2019 12:00			  */
/*				Version	: 1.0.0.0				  */
/*										  */
/**********************************************************************************/

class webalizer {
	private $html;
	private $tables;

	// Webalizer class constructor.
	function __construct ($filename) {
		if (file_exists($filename)) {
    		$this->html 	= file_get_contents($filename);
    		$this->tables 	= $this->extract_tables($this->html);
    	} else {
    		$this->html 	= null;
    		$this->tables 	= null;
    	}
	}

	// Extract tables from HTML.
	private function extract_tables ($html) {
		if (preg_match_all('/(?<=<TABLE WIDTH=510 BORDER=2 CELLSPACING=1 CELLPADDING=1>)(?P<tables>.*?)(?=<\/TABLE>)/is', $html, $result)) {
			return $result['tables'];
		} else {
			return [];
		}
	}

	// Extract days from Table.
	private function extract_days ($table) {
		if (preg_match_all('/(?<=<\/B><\/FONT><\/TD>)(?P<days>.*?)(?=<\/TR>)/is', $table, $result)) {
			return $result['days'];
		} else {
			return [];
		}
	}

	// Extract rows from Table.
	private function extract_rows ($table) {
		if (preg_match_all('/(?<=<TR><TH HEIGHT=4><\/TH><\/TR>)(?P<rows>.*?)(?=<TR><TH HEIGHT=4><\/TH><\/TR>)/is', $table, $result)) {
			if (preg_match_all('/(?<=<TR>)(?P<rows>.*?)(?=<\/TR>\n)/is', end($result['rows']), $_result)) {
				return $_result['rows'];
			} else {
				return [];
			}
		}
	}

	private function extract_hits ($table, $label) {
		$rows = $this->extract_rows($table);
		$output = [];
		foreach ($rows as $row) {
			$_row = [];
			if (preg_match_all('/(?<=<TD ALIGN=right><FONT SIZE="-1"><B>)(?P<count>.*?)(?=<\/B><\/FONT><\/TD>)/i', $row, $result)) {
				$_row['hits']['count'] = intval($result['count'][0]);
				$_row[$label]['count'] = intval($result['count'][1]);
			}
			if (preg_match_all('/(?<=<TD ALIGN=right><FONT SIZE="-2">)(?P<percent>.*?)(?=%<\/FONT><\/TD>)/i', $row, $result)) {
				$_row['hits']['percent'] = floatval($result['percent'][0]);
				$_row[$label]['percent'] = floatval($result['percent'][1]);
			}
			if (preg_match('/(?<=<A HREF=")(?P<url>.*?)(?=">\/)/i', $row, $result)) {
				$_row['url'] = $result['url'];
			}
			array_push($output, $_row);
		}
		return $output;
	}

	private function extract_single_hits ($table, $label) {
		$rows = $this->extract_rows($table);
		$output = [];
		foreach ($rows as $row) {
			$_row = [];
			if (preg_match('/(?<=<TD ALIGN=right><FONT SIZE="-1"><B>)(?P<hits>.*?)(?=<\/B><\/FONT><\/TD>)/i', $row, $result)) {
				$_row['hits'] = intval($result['hits']);
			}
			if (preg_match('/(?<=<TD ALIGN=right><FONT SIZE="-2">)(?P<percent>.*?)(?=%<\/FONT><\/TD>)/i', $row, $result)) {
				$_row['percent'] = floatval($result['percent']);
			}
			if (preg_match('/(?<=<TD ALIGN=left NOWRAP><FONT SIZE="-1">)(?P<url>.*?)(?=<\/FONT>)/i', $row, $result)) {
				$_row[$label] = $result['url'];
			}
			array_push($output, $_row);
		}
		return $output;
	}

	// Find the right table.
	private function array_search_partial ($arr, $keyword) {
	    foreach($arr as $index => $string) {
	        if (strpos($string, $keyword) !== false) {
	            return $index;
	        }
	    }
	}

	// Monthly.
	function monthly () {
		if ($this->tables === null) {
			return false;
		}
		$lines = preg_split('/\r\n|\r|\n/', $this->tables[0]);
		$output = ['monthly' => [], 'responseCode' => []];
		foreach ($lines as $line) {
			if (preg_match('/(?<=<TR><TD WIDTH=380><FONT SIZE="-1">)(?P<title>.*?)(?=<\/FONT><\/TD>)/i', $line, $result)) {
				$title = $result['title'];
			}
			if (preg_match('/(?<=<TD ALIGN=right COLSPAN=2><FONT SIZE="-1"><B>)(?P<value>.*?)(?=<\/B><\/FONT><\/TD><\/TR>)/i', $line, $result)) {
				$value = intval($result['value']);
			}
			if (!empty($title) && !empty($value)) {
				array_push($output['monthly'], ['title' => $title, 'total' => $value]);
				$title = '';
				$value = '';
			}
			if (preg_match('/(?<=<TR><TD><FONT SIZE="-1">)(?P<title>.*?)(?=<\/FONT><\/TD>)/i', $line, $result)) {
				$title = $result['title'];
			}
			if (preg_match('/(?<=<TD ALIGN=right WIDTH=65><FONT SIZE="-1"><B>)(?P<average>.*?)(?=<\/B><\/FONT><\/TD>)/i', $line, $result)) {
				$average = intval($result['average']);
			}
			if (preg_match('/(?<=<TD WIDTH=65 ALIGN=right><FONT SIZE=-1><B>)(?P<max>.*?)(?=<\/B><\/FONT><\/TD><\/TR>)/i', $line, $result)) {
				$max = intval($result['max']);
			}
			if (!empty($title) && !empty($average) && !empty($max)) {
				array_push($output['monthly'], ['title' => $title, 'average' => $average, 'max' => $max]);
				$title 	 = '';
				$average = '';
				$max	 = '';
			}
			if (preg_match('/(?<=<TR><TD><FONT SIZE="-1">)(?P<title>.*?)(?=<\/FONT><\/TD>)/i', $line, $result)) {
				$title = $result['title'];
			}
			if (preg_match('/(?<=<TD ALIGN=right><FONT SIZE="-2">)(?P<percent>.*?)(?=%<\/FONT><\/TD>)/i', $line, $result)) {
				$percent = floatval($result['percent']);
			}
			if (preg_match('/(?<=<TD ALIGN=right><FONT SIZE="-1"><B>)(?P<total>.*?)(?=<\/B><\/FONT><\/TD><\/TR>)/i', $line, $result)) {
				$total = intval($result['total']);
			}
			if (!empty($title) && !empty($percent) && !empty($total)) {
				array_push($output['responseCode'], ['title' => $title, 'percent' => $percent, 'total' => $total]);
				$title 	 = '';
				$percent = '';
				$total	 = '';
			}
		}
		return $output;
	}

	// Daily.
	function daily () {
		if ($this->tables === null) {
			return false;
		}
		$days   = $this->extract_days($this->tables[1]);
		$output = [];
		$i 		= 1;
		foreach ($days as $day) {
			$rows = preg_split('/\r\n|\r|\n/', $day);
			$r = 1;
			$_day = [];
			foreach ($rows as $row) {
				if (preg_match('/(?<=<TD ALIGN=right><FONT SIZE="-1"><B>)(?P<count>.*?)(?=<\/B><\/FONT><\/TD>)/i', $row, $result)) {
					$count = intval($result['count']);
				}
				if (preg_match('/(?<=<TD ALIGN=right><FONT SIZE="-2">)(?P<percent>.*?)(?=%<\/FONT><\/TD>)/i', $row, $result)) {
					$percent = floatval($result['percent']);
				}
				if ($r % 2 !== 0) {
					switch ($r) {
						case 3:  $_day['hits']   = ['count' => $count, 'percent' => $percent]; break;
						case 5:  $_day['files']  = ['count' => $count, 'percent' => $percent]; break;
						case 7:  $_day['pages']  = ['count' => $count, 'percent' => $percent]; break;
						case 9:  $_day['visits'] = ['count' => $count, 'percent' => $percent]; break;
						case 11: $_day['sites']  = ['count' => $count, 'percent' => $percent]; break;
						case 13: $_day['kbytes'] = ['count' => $count, 'percent' => $percent]; break;
					}
				}
				$r++;
			}
			$_day['day'] = $i;
			array_push($output, $_day);
			$i++;
		}
		return $output;
	}

	// Top 10 Hits.
	function top_10 () {
		return $this->extract_hits($this->tables[4], 'kbytes');
	}

	// Top 30 Hits.
	function top_30 () {
		return $this->extract_hits($this->tables[3], 'kbytes');
	}

	// Top Entry.
	function top_entry () {
		return $this->extract_hits($this->tables[5], 'visits');
	}

	// Top Exit.
	function top_exit () {
		return $this->extract_hits($this->tables[6], 'visits');
	}

	// Referer.
	function referer () {
		return $this->extract_single_hits($this->tables[9], 'url');
	}

	// User-Agents.
	function useragents () {
		return $this->extract_single_hits($this->tables[$this->array_search_partial($this->tables, 'Total User Agents')], 'useragents');
	}

}

?>
