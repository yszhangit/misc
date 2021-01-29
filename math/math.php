<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<html>
<head><title>easy math</title>
<style>
div.container { width:1024; height:1280; }
span.dt { float:right; text-align:right; font-size:15px; }
span.score { text-align:left; font-size:40px; padding:20px; }
div.line { margin-top:40px; margin-bottom:30px; border-bottom:solid 3px #333; }
div.page_break { page-break-before: always; }
td { text-align:right; font-size:50px; padding:0 3px 0 3px; }
div.cnt { text-align:center; font-size:15px; height:20px; width:20px; color:white; float:left; background-image:url('../images/20x20_black.gif'); }
div.small { font-size:30px; text-align:left; margin-left:30px; }
div.d_cell { position:relative; width:200px; height:200px; padding:5px; margin:20px; border:dashed 1px #777; }
div.d_cell_small { position:relative; width:200px; height:40px; padding:5px; margin:12px; border:dashed 1px #777; }
div.d_q { position:absolute; right:0px; margin-right:40px; top:10px; width:100px; z-index:19; }
div.d_res { position:absolute; top:0px; right:20px; left:40px; z-index:10; border-bottom:solid 3px black; height:130px; }
span.answer { display:none; }
img#img_strike{ margin-left:4em; margin-right:8em; }
@media print{ .noprint { display:none;} }
</style>
<script>
function showAnswer() {
	var elements = document.getElementsByClassName("answer");
	for (var i = 0; i < elements.length; i++) {
		elements[i].style.display = elements[i].style.display == 'inline' ? 'none' : 'inline';
    }
}
</script>
</head>
<body>
<?php
//define(DEBUG,true);
require_once('lib.php');
$limit_num1_min=(int)$_GET['n1_min'];
$limit_num1_max=(int)$_GET['n1_max'];
$limit_num2_min=(int)$_GET['n2_min'];
$limit_num2_max=(int)$_GET['n2_max'];
$limit_result=(int)$_GET['res'];
$operator=$_GET['oper'];
$comparsion=$_GET['comp'];
$carry=$_GET['carry'];
$pages=$_GET['pages'];
define("PERPAGE",$_GET['perpage']);

if (!($operator == 'add' || $operator == 'sub' || $operator == 'mul' || $operator == 'div'))
	die("operator should be either +, -, x, or /");

if ($comparsion == 'lt')
	$comparsion = '<';
else if ($comparsion == 'gt')
	$comparsion = '>';
else
	die("comparsion should be either > or < ");
$arr_carry=array('full','partal','no','yes','ignore');

if (! in_array($carry,$arr_carry)) {
	die("invlid carry option, chose full,partal, and no");
}


$number_of_questions=PERPAGE*$pages;
$number_of_tries=1000*$pages;
$questions=0;
$arr_questions=array();

for($i=1;$i<=$number_of_tries;$i++) {
	$num1=rand($limit_num1_min,$limit_num1_max);
	$num2=rand($limit_num2_min,$limit_num2_max);
	$question=new question($num1,$num2,$operator);
	deb($question->to_string());
	if (!$question->validate_carry($carry)){
		deb("failed carry condition");
		continue;
	}
	if ($question->validate($limit_result,$comparsion)) {
		// avoid same question
		$duplicated=false;
		foreach($arr_questions as $q) {
			if ($q->equal($question)) {
				$duplicated=true;
				deb('--duplicated');
				break;
			}
		}
		if (!$duplicated) {
			array_push($arr_questions,$question);
			$questions++;
			if ($questions>=$number_of_questions) {
				break;
			}
		}
	}else{
		deb('out of limit');
	}
}

echo '<button class="noprint" onclick="showAnswer()">Toggle Answers</button>';

if ($i>=$number_of_tries && $questions<$number_of_questions) {
	echo "<p> can not generate enough question, review limits.</p>";
}

// print each page
for($i=1;$i<=$pages;$i++) {
	// skil page break at first page
	if ($i <> 1)
		echo "<div class=page_break>&nbsp;</div>";
	echo "<div class=container>";
	echo "<span class=dt>".date('D j-M-Y')."</span>";
	echo "<div class=line>&nbsp;</div>";
	print_table($i);
	echo "</div>";
}

// print table in a page;
function print_table($pg) {
	global $arr_questions;
	echo "<table width=100%>";
	$cols=4;
	$from=($pg-1)*PERPAGE;
	$to=$pg*PERPAGE;
	if ($to>sizeof($arr_questions))
		$to=sizeof($arr_questions);
	for($i=$from;$i<$to;$i++) {
		if ( $i % $cols == 0) {
			if ($i<>0)
				echo "</tr>";
			echo "\n<tr>\n";
		}
		echo "<td class=cell>";
		$arr_questions[$i]->to_html($i+1);
		echo "</td>";
	}
	echo "</tr></table>";
}


class question {
	var $num1;
	var $num2;
	var $result;
	var $operator;
	var $carry;	// full, partal, no

	public function __construct ($n1,$n2,$o) {
		// num1 always > num2
		if ($n1>$n2) {
			$this->num1=$n1;
			$this->num2=$n2;
		}else{
			$this->num1=$n2;
			$this->num2=$n1;
		}
		$this->operator=$o;
		// calculte result
		if ($o=='add') {
			$this->result=$n1+$n2;
			$this->is_carry($n1,$n2);
		} else if ($o=='mul') {
			$this->result=$n1*$n2;
		} else if ($o=='div') {
			if ($n2==0) {
				$this->result=0;
				// validate result will return false, so that this set
				// of number will skipped
			}else{	
				$this->result=$n1/$n2;
			}
		} else{
			$this->result=$this->num1-$this->num2;
			$this->is_carry($this->result,$n2);
		}
	}

	public function to_string() {
		return $this->num1.$this->operator.$this->num2.":".$this->carry;
	}
	private function is_carry($in1,$in2) {
		if ($in1>$in2) {
			$n1=$in1;
			$n2=$in2;
		}else{
			$n1=$in2;
			$n2=$in1;
		}
		$len=strlen($n2);
		$res=$n1+$n2;

		$carry=false;
		$noncarry=false;
		for($i=0;$i<$len;$i++) {
			$nn1=(int)substr($n1,$len-$i,1);
			$nn2=(int)substr($n2,$len-$i,1);
			$r=(int)substr($this->result,$len-$i,1);
			if ( $nn1 >= $r && $nn2 >= $r ) {
				$carry=true;
				deb("--$nn1,$nn2 carry");
			}else{
				$noncarry=true;
				deb("--$nn1,$nn2 not carry");
			}
		}
		if ($carry && $noncarry)
			$this->carry="partal";
		else if ($carry && !$noncarry)
			$this->carry="full";
		else
			$this->carry="no";

	}
	public function equal($another) {
		if ($this->num1==$another->num1 && $this->num2==$another->num2)
		{
			return true;
		}else {
			return false;
		}
	}
	public function validate($limit,$comparsion) {
		if ($this->operator=='div') {
			if ($this->num2==0) {
				return false;
			}
			if ($this->num1%$this->num2 <> 0 ) {
				return false;
			}
		}
		if ($comparsion=='<') {
			return $this->result<$limit;
		}
		if ($comparsion=='>') {
			return $this->result>$limit;
		}
	}

	public function validate_carry($in) {
		// mul and div ignore carry
		if ($this->operator=='mul' || $this->operator=='div') {
			return true;
		}
		if ($in==$this->carry || $in=='ignore') {
			return true;
		}
		if ($in=='yes' && ($this->carry=='full' || $this->carry=='partal')) {
			return true;
		}
		return false;
	}

	public function to_html($cnt) {
		global $answer;
		echo "\n<div class=cnt>$cnt</div>";
		echo "\n<div class=d_cell_small>";
		if ($this->operator=='div')
			//echo "\n<div class=small>".$this->result.chr(264).$this->num1."=</div>";
			echo "\n<div class=small>".$this->num1.'÷'.$this->num2."=<span class=answer>".$this->result."</span></div>";
		else if ($this->operator=='add')
			echo "\n<div class=small>".$this->num1.'+'.$this->num2."=<span class=answer>".$this->result."</span></div>";
		else if ($this->operator=='sub')
			echo "\n<div class=small>".$this->num1.'-'.$this->num2."=<span class=answer>".$this->result."</span></div>";
		else if ($this->operator=='mul')
			echo "\n<div class=small>".$this->num1.'x'.$this->num2."=<span class=answer>".$this->result."</span></div>";
		else
			echo "error";
		echo "\n</div>";
	}
}

?>
</body>
</html>
