<?php

define('WP_IDENTICON_DIR',  DW_IDENTICON_SUBSITE_DIR . DIRECTORY_SEPARATOR . DW_IDENTICON_CACHE_DIR);
define('WP_IDENTICON_DIR_INTERNAL', WP_IDENTICON_ROOT_PATH . DIRECTORY_SEPARATOR . DW_IDENTICON_CACHE_DIR);

class identicon {
	var $identicon_options;
	var $blocks;
	var $shapes;
	var $rotatable;
	var $square;
	var $im;
	var $colors;
	var $size;
	var $blocksize;
	var $quarter;
	var $half;
	var $diagonal;
	var $halfdiag;
	var $transparent=false;
	var $centers;
	var $shapes_mat;
	var $symmetric_num;
	var $rot_mat;
	var $invert_mat;
	var $rotations;

	//constructor
	function identicon($options = null) {

        if (!isset($options['size'])|!isset($options['backb'])){
            //Set Default Values Here
            $options=array('size'=>35,'backr'=>array(255,255),'backg'=>array(255,255),'backb'=>array(255,255), 'forer'=>array(1,255),'foreg'=>array(1,255),'foreb'=>array(1,255),'squares'=>4,'autoadd'=>1,'gravatar'=>0,'grey'=>0);
        }

		$this->identicon_options=$options;
		$this->blocks=$this->identicon_options['squares'];
		$this->blocksize=80;
		$this->size=$this->blocks*$this->blocksize;
		$this->quarter=$this->blocksize/4;
		$this->half=$this->blocksize/2;
		$this->diagonal=sqrt($this->half*$this->half+$this->half*$this->half);
		$this->halfdiag=$this->diagonal/2;
		$this->shapes=array(
			array(array(array(90,$this->half),array(135,$this->diagonal),array(225,$this->diagonal),array(270,$this->half))),//0 rectangular half block
			array(array(array(45,$this->diagonal),array(135,$this->diagonal),array(225,$this->diagonal),array(315,$this->diagonal))),//1 full block
			array(array(array(45,$this->diagonal),array(135,$this->diagonal),array(225,$this->diagonal))),//2 diagonal half block
			array(array(array(90,$this->half),array(225,$this->diagonal),array(315,$this->diagonal))),//3 triangle
			array(array(array(0,$this->half),array(90,$this->half),array(180,$this->half),array(270,$this->half))),//4 diamond
			array(array(array(0,$this->half),array(135,$this->diagonal),array(270,$this->half),array(315,$this->diagonal))),//5 stretched diamond
			array(array(array(0,$this->quarter),array(90,$this->half),array(180,$this->quarter)), array(array(0,$this->quarter),array(315,$this->diagonal),array(270,$this->half)), array(array(270,$this->half),array(180,$this->quarter),array(225,$this->diagonal))),// 6 triple triangle
			array(array(array(0,$this->half),array(135,$this->diagonal),array(270,$this->half))),//7 pointer
			array(array(array(45,$this->halfdiag),array(135,$this->halfdiag),array(225,$this->halfdiag),array(315,$this->halfdiag))),//9 center square
			array(array(array(180,$this->half),array(225,$this->diagonal),array(0,0)), array(array(45,$this->diagonal),array(90,$this->half),array(0,0))),//9 double triangle diagonal
			array(array(array(90,$this->half),array(135,$this->diagonal),array(180,$this->half),array(0,0))),//10 diagonal square
			array(array(array(0,$this->half),array(180,$this->half),array(270,$this->half))),//11 quarter triangle out
			array(array(array(315,$this->diagonal),array(225,$this->diagonal),array(0,0))),//12quarter triangle in
			array(array(array(90,$this->half),array(180,$this->half),array(0,0))),//13 eighth triangle in
			array(array(array(90,$this->half),array(135,$this->diagonal),array(180,$this->half))),//14 eighth triangle out
			array(array(array(90,$this->half),array(135,$this->diagonal),array(180,$this->half),array(0,0)), array(array(0,$this->half),array(315,$this->diagonal),array(270,$this->half),array(0,0))),//15 double corner square
			array(array(array(315,$this->diagonal),array(225,$this->diagonal),array(0,0)), array(array(45,$this->diagonal),array(135,$this->diagonal),array(0,0))),//16 double quarter triangle in
			array(array(array(90,$this->half),array(135,$this->diagonal),array(225,$this->diagonal))),//17 tall quarter triangle
			array(array(array(90,$this->half),array(135,$this->diagonal),array(225,$this->diagonal)), array(array(45,$this->diagonal),array(90,$this->half),array(270,$this->half))),//18 double tall quarter triangle
			array(array(array(90,$this->half),array(135,$this->diagonal),array(225,$this->diagonal)), array(array(45,$this->diagonal),array(90,$this->half),array(0,0))),//19 tall quarter + eighth triangles
			array(array(array(135,$this->diagonal),array(270,$this->half),array(315,$this->diagonal))),//20 tipped over tall triangle
			array(array(array(180,$this->half),array(225,$this->diagonal),array(0,0)), array(array(45,$this->diagonal),array(90,$this->half),array(0,0)), array(array(0,$this->half),array(0,0),array(270,$this->half))),//21 triple triangle diagonal
			array(array(array(0,$this->quarter),array(315,$this->diagonal),array(270,$this->half)), array(array(270,$this->half),array(180,$this->quarter),array(225,$this->diagonal))),//22 double triangle flat
			array(array(array(0,$this->quarter),array(45,$this->diagonal),array(315,$this->diagonal)), array(array(180,$this->quarter),array(135,$this->diagonal),array(225,$this->diagonal))),//23 opposite 8th triangles
			array(array(array(0,$this->quarter),array(45,$this->diagonal),array(315,$this->diagonal)), array(array(180,$this->quarter),array(135,$this->diagonal),array(225,$this->diagonal)), array(array(180,$this->quarter),array(90,$this->half),array(0,$this->quarter),array(270,$this->half))),//24 opposite 8th triangles + diamond
			array(array(array(0,$this->quarter),array(90,$this->quarter),array(180,$this->quarter),array(270,$this->quarter))),//25 small diamond
			array(array(array(0,$this->quarter),array(45,$this->diagonal),array(315,$this->diagonal)), array(array(180,$this->quarter),array(135,$this->diagonal),array(225,$this->diagonal)), array(array(270,$this->quarter),array(225,$this->diagonal),array(315,$this->diagonal)),array(array(90,$this->quarter),array(135,$this->diagonal),array(45,$this->diagonal))),//26 4 opposite 8th triangles
			array(array(array(315,$this->diagonal),array(225,$this->diagonal),array(0,0)), array(array(0,$this->half),array(90,$this->half),array(180,$this->half))),//27 double quarter triangle parallel
			array(array(array(135,$this->diagonal),array(270,$this->half),array(315,$this->diagonal)), array(array(225,$this->diagonal),array(90,$this->half),array(45,$this->diagonal))),//28 double overlapping tipped over tall triangle
			array(array(array(90,$this->half),array(135,$this->diagonal),array(225,$this->diagonal)), array(array(315,$this->diagonal),array(45,$this->diagonal),array(270,$this->half))),//29 opposite double tall quarter triangle
			array(array(array(0,$this->quarter),array(45,$this->diagonal),array(315,$this->diagonal)), array(array(180,$this->quarter),array(135,$this->diagonal),array(225,$this->diagonal)), array(array(270,$this->quarter),array(225,$this->diagonal),array(315,$this->diagonal)),array(array(90,$this->quarter),array(135,$this->diagonal),array(45,$this->diagonal)),array(array(0,$this->quarter),array(90,$this->quarter),array(180,$this->quarter),array(270,$this->quarter))),//30 4 opposite 8th triangles+tiny diamond
			array(array(array(0,$this->half),array(90,$this->half),array(180,$this->half),array(270,$this->half), array(270,$this->quarter),array(180,$this->quarter),array(90,$this->quarter),array(0,$this->quarter))),//31 diamond C
			array(array(array(0,$this->quarter),array(90,$this->half),array(180,$this->quarter),array(270,$this->half))),//32 narrow diamond
			array(array(array(180,$this->half),array(225,$this->diagonal),array(0,0)), array(array(45,$this->diagonal),array(90,$this->half),array(0,0)), array(array(0,$this->half),array(0,0),array(270,$this->half)), array(array(90,$this->half),array(135,$this->diagonal),array(180,$this->half))),//33 quadruple triangle diagonal
			array(array(array(0,$this->half),array(90,$this->half),array(180,$this->half),array(270,$this->half),array(0,$this->half), array(0,$this->quarter),array(270,$this->quarter),array(180,$this->quarter),array(90,$this->quarter),array(0,$this->quarter))),//34 diamond donut
			array(array(array(90,$this->half),array(45,$this->diagonal),array(0,$this->quarter)), array(array(0,$this->half),array(315,$this->diagonal),array(270,$this->quarter)), array(array(270,$this->half),array(225,$this->diagonal),array(180,$this->quarter))),//35 triple turning triangle
			array(array(array(90,$this->half),array(45,$this->diagonal),array(0,$this->quarter)), array(array(0,$this->half),array(315,$this->diagonal),array(270,$this->quarter))),//36 double turning triangle
			array(array(array(90,$this->half),array(45,$this->diagonal),array(0,$this->quarter)), array(array(270,$this->half),array(225,$this->diagonal),array(180,$this->quarter))),//37 diagonal opposite inward double triangle
			array(array(array(90,$this->half),array(225,$this->diagonal),array(0,0),array(315,$this->diagonal))),//38 star fleet
			array(array(array(90,$this->half),array(225,$this->diagonal),array(0,0),array(315,$this->halfdiag),array(225,$this->halfdiag), array(225,$this->diagonal),array(315,$this->diagonal))),//39 hollow half triangle
			array(array(array(90,$this->half),array(135,$this->diagonal),array(180,$this->half)), array(array(270,$this->half),array(315,$this->diagonal),array(0,$this->half))),//40 double eighth triangle out
			array(array(array(90,$this->half),array(135,$this->diagonal),array(180,$this->half),array(180,$this->quarter)), array(array(270,$this->half),array(315,$this->diagonal),array(0,$this->half),array(0,$this->quarter))),//42 double slanted square
			array(array(array(0,$this->half),array(45,$this->halfdiag), array(0,0),array(315,$this->halfdiag)), array(array(180,$this->half),array(135,$this->halfdiag), array(0,0),array(225,$this->halfdiag))),//43 double diamond
			array(array(array(0,$this->half),array(45,$this->diagonal), array(0,0),array(315,$this->halfdiag)), array(array(180,$this->half),array(135,$this->halfdiag), array(0,0),array(225,$this->diagonal))),//44 double pointer
		);
		$this->rotatable=array(1,4,8,25,26,30,34);
		$this->square=$this->shapes[1][0];	
		$this->symmetric_num=ceil($this->blocks*$this->blocks/4);
		for ($i=0;$i<$this->blocks;$i++){
			for ($j=0;$j<$this->blocks;$j++){
				$this->centers[$i][$j]=array($this->half+$this->blocksize*$j,$this->half+$this->blocksize*$i);
				$this->shapes_mat[$this->xy2symmetric($i,$j)]=1;
				$this->rot_mat[$this->xy2symmetric($i,$j)]=0;
				$this->invert_mat[$this->xy2symmetric($i,$j)]=0;
				if (floor(($this->blocks-1)/2-$i)>=0&floor(($this->blocks-1)/2-$j)>=0&($j>=$i|$this->blocks%2==0)){
					$inversei=$this->blocks-1-$i;
					$inversej=$this->blocks-1-$j;
					$symmetrics=array(array($i,$j),array($inversej,$i),array($inversei,$inversej),array($j,$inversei));
					$fill=array(0,270,180,90);
					for ($k=0;$k<count($symmetrics);$k++){
						$this->rotations[$symmetrics[$k][0]][$symmetrics[$k][1]]=$fill[$k];
					}
				}
			}
		}
	}
	
	function xy2symmetric($x,$y){
		$index=array(floor(abs(($this->blocks-1)/2-$x)),floor(abs(($this->blocks-1)/2-$y)));
		sort($index);
		$index[1]*=ceil($this->blocks/2);
		$index=array_sum($index);
		return $index;
	}
	


	//convert array(array(heading1,distance1),array(heading1,distance1)) to array(x1,y1,x2,y2)
	function identicon_calc_x_y($array,$centers,$rotation=0){
		$output=array();
		$centerx=$centers[0];
		$centery=$centers[1];
		while($thispoint=array_pop($array)){
			$y=round($centery+sin(deg2rad($thispoint[0]+$rotation))*$thispoint[1]);
			$x=round($centerx+cos(deg2rad($thispoint[0]+$rotation))*$thispoint[1]);
			array_push($output,$x,$y);
		}
		return $output;
	}

	//draw filled polygon based on an array of (x1,y1,x2,y2,..)
	function identicon_draw_shape($x,$y){ 
		$index=$this->xy2symmetric($x,$y);
		$shape=$this->shapes[$this->shapes_mat[$index]];
		$invert=$this->invert_mat[$index];
		$rotation=$this->rot_mat[$index];
		$centers=$this->centers[$x][$y];
		$invert2=abs($invert-1);
		$points=$this->identicon_calc_x_y($this->square,$centers,0);
		$num = count($points) / 2;
		imagefilledpolygon($this->im, $points, $num, $this->colors[$invert2]);
		foreach($shape as $subshape){
			$points=$this->identicon_calc_x_y($subshape,$centers,$rotation+$this->rotations[$x][$y]);
			$num = count($points) / 2;
			imagefilledpolygon($this->im, $points, $num,$this->colors[$invert]);
		}
	}

	//use a seed value to determine shape, rotation, and color
	function identicon_set_randomness($seed=""){
		//set seed
		$twister=new identicon_mersenne_twister(hexdec($seed));
		foreach ($this->rot_mat as $key => $value){
			$this->rot_mat[$key]=$twister->rand(0,3)*90;
			$this->invert_mat[$key]=$twister->rand(0,1);
			#&$this->blocks%2
			if ($key==0) $this->shapes_mat[$key]=$this->rotatable[$twister->array_rand($this->rotatable)];
			else $this->shapes_mat[$key]=$twister->array_rand($this->shapes);
		}
		$forecolors=array($twister->rand($this->identicon_options['forer'][0],$this->identicon_options['forer'][1]), $twister->rand($this->identicon_options['foreg'][0],$this->identicon_options['foreg'][1]), $twister->rand($this->identicon_options['foreb'][0],$this->identicon_options['foreb'][1]));
		$this->colors[1]=imagecolorallocate($this->im, $forecolors[0],$forecolors[1],$forecolors[2]);
		if (array_sum($this->identicon_options['backr']) + array_sum($this->identicon_options['backg']) + array_sum($this->identicon_options['backb'])==0) {
			$this->colors[0]=imagecolorallocatealpha($this->im,0,0,0,127);
			$this->transparent=true;
			imagealphablending ($this->im,false);
			imagesavealpha($this->im,true);
		} else {
			$backcolors=array($twister->rand($this->identicon_options['backr'][0],$this->identicon_options['backr'][1]), $twister->rand($this->identicon_options['backg'][0],$this->identicon_options['backg'][1]), $twister->rand($this->identicon_options['backb'][0],$this->identicon_options['backb'][1]));
			$this->colors[0]=imagecolorallocate($this->im, $backcolors[0],$backcolors[1],$backcolors[2]);
		}
		if($this->identicon_options['grey']){
			$this->colors[1]=imagecolorallocate($this->im, $forecolors[0],$forecolors[0],$forecolors[0]);
			if(!$this->transparent) $this->colors[0]=imagecolorallocate($this->im, $backcolors[0],$backcolors[0],$backcolors[0]);
		}
		return true;
	}

	function identicon_build($seed='',$altImgText='',$img=true,$outsize='',$write=true,$random=true,$displaysize='',$gravataron=true){
		//make an identicon and return the filepath or if write=false return picture directly
		if (function_exists("gd_info")){
			// init random seed
			$id=substr($seed, 0, 10);
			$filename=md5($id) . '.png';
			if ($outsize=='') $outsize=$this->identicon_options['size'];
			if($displaysize=='') $displaysize=$outsize;
            $fileLocalPath = WP_IDENTICON_DIR_INTERNAL.DIRECTORY_SEPARATOR.$filename;
			if (!file_exists($fileLocalPath)){
				$this->im = imagecreatetruecolor($this->size,$this->size);	
				$this->colors = array(imagecolorallocate($this->im, 255,255,255));
				if ($random) $this->identicon_set_randomness($id);
				else {$this->colors = array(imagecolorallocate($this->im, 255,255,255),imagecolorallocate($this->im, 0,0,0));$this->transparent=false;};
				imagefill($this->im,0,0,$this->colors[0]);
				for ($i=0;$i<$this->blocks;$i++){
					for ($j=0;$j<$this->blocks;$j++){
					$this->identicon_draw_shape($i,$j);
					}
				}

				$out = @imagecreatetruecolor($outsize,$outsize);
				imagesavealpha($out,true);
				imagealphablending($out,false);
				imagecopyresampled($out,$this->im,0,0,0,0,$outsize,$outsize,$this->size,$this->size);
				imagedestroy($this->im);
				if ($write){
						$wrote=@imagepng($out,WP_IDENTICON_DIR_INTERNAL.DIRECTORY_SEPARATOR.$filename);
						if(!$wrote) return false; //something went wrong but don't want to mess up blog layout
				}else{
					header ("Content-type: image/png");
					imagepng($out);
				}
				imagedestroy($out);

                $this->limitCache();
			}
            else
            {
                touch($fileLocalPath);
            }
			$processedFileName=WP_IDENTICON_DIR.DIRECTORY_SEPARATOR.$filename;
			if($this->identicon_options['gravatar']&&$gravataron)
                $processedFileName = "http://www.gravatar.com/avatar.php?gravatar_id=".$seed."&amp;size=$outsize&amp;default=$processedFileName";
			if ($img){
                $processedFileName='<img class="identicon" src="'.$processedFileName.'" alt="'.str_replace('"',"'",$altImgText).' Identicon" height="'.$displaysize.'" width="'.$displaysize.'" />';
			}
            else
            {
                $processedFileName = WP_IDENTICON_DIR_INTERNAL.DIRECTORY_SEPARATOR.$filename;
            }
			return $processedFileName;
		} else { //php GD image manipulation is required
			return false; //php GD image isn't installed but don't want to mess up blog layout
		}
	}

    function limitCache($maxCount = 512, $extensions = ['png', 'jpg', 'jpeg', 'bmp', 'gif'])
    {
        if (DEBUG)
        {
            $maxCount = 7;
        }

        $glob = WP_IDENTICON_DIR_INTERNAL.DIRECTORY_SEPARATOR.'*.{' . implode(',', $extensions) . '}';
        $files = glob($glob, GLOB_BRACE|GLOB_NOSORT);
        array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_ASC, $files);

        $fileCount = count($files);
        if ($fileCount > $maxCount)
        {
            $deleteFiles = array_slice($files, 0, $fileCount - $maxCount);
            array_map('unlink', $deleteFiles);
        }
        return $deleteFiles;
    }
}

class identicon_mersenne_twister{
//MySQL version doesn't work since they shut down integer overflow switching to:
//https://github.com/ruafozy/php-mersenne-twister/blob/master/src/mersenne_twister.php

  function identicon_mersenne_twister($seed=123456) {
    $this->bits32 = PHP_INT_MAX == 2147483647;
	 $this->define_constants();
	 $this->init_with_integer($seed);
  }

  function define_constants() {
	  $this->N = 624;
	  $this->M = 397;
	  $this->MATRIX_A = 0x9908b0df;
	  $this->UPPER_MASK = 0x80000000;
	  $this->LOWER_MASK = 0x7fffffff;

	  $this->MASK10=~((~0) << 10); 
	  $this->MASK11=~((~0) << 11); 
	  $this->MASK12=~((~0) << 12); 
	  $this->MASK14=~((~0) << 14); 
	  $this->MASK20=~((~0) << 20); 
	  $this->MASK21=~((~0) << 21); 
	  $this->MASK22=~((~0) << 22); 
	  $this->MASK26=~((~0) << 26); 
	  $this->MASK27=~((~0) << 27); 
	  $this->MASK31=~((~0) << 31); 

	  $this->TWO_TO_THE_16=pow(2,16);
	  $this->TWO_TO_THE_31=pow(2,31);
	  $this->TWO_TO_THE_32=pow(2,32);

	  $this->MASK32 = $this->MASK31 | ($this->MASK31 << 1);
  }

  function init_with_integer($integer_seed) {
    $integer_seed = $this->force_32_bit_int($integer_seed);

    $mt = &$this->mt;
    $mti = &$this->mti;

    $mt = array_fill(0, $this->N, 0);

    $mt[0] = $integer_seed;

    for($mti = 1; $mti < $this->N; $mti++) {
      $mt[$mti] = $this->add_2($this->mul(1812433253,
        ($mt[$mti - 1] ^ (($mt[$mti - 1] >> 30) & 3))), $mti);
      /*
      mt[mti] =
          (1812433253UL * (mt[mti-1] ^ (mt[mti-1] >> 30)) + mti);
      */
    }
  }

   /* generates a random number on [0,1)-real-interval */
  function real_halfopen() {
	  return
		  $this->signed2unsigned($this->int32()) * (1.0 / 4294967296.0);
  }
  function int32() {
    $mag01 = array(0, $this->MATRIX_A);

    $mt = &$this->mt;
    $mti = &$this->mti;

    if ($mti >= $this->N) { /* generate N words all at once */
        for ($kk=0;$kk<$this->N-$this->M;$kk++) {
            $y = ($mt[$kk]&$this->UPPER_MASK)|($mt[$kk+1]&$this->LOWER_MASK);
            $mt[$kk] = $mt[$kk+$this->M] ^ (($y >> 1) & $this->MASK31) ^ $mag01[$y & 1];
        }
        for (;$kk<$this->N-1;$kk++) {
            $y = ($mt[$kk]&$this->UPPER_MASK)|($mt[$kk+1]&$this->LOWER_MASK);
            $mt[$kk] =
              $mt[$kk+($this->M-$this->N)] ^ (($y >> 1) & $this->MASK31) ^ $mag01[$y & 1];
        }
        $y = ($mt[$this->N-1]&$this->UPPER_MASK)|($mt[0]&$this->LOWER_MASK);
        $mt[$this->N-1] = $mt[$this->M-1] ^ (($y >> 1) & $this->MASK31) ^ $mag01[$y & 1];

        $mti = 0;
    }
  
    $y = $mt[$mti++];

    /* Tempering */
    $y ^= ($y >> 11) & $this->MASK21;
    $y ^= ($y << 7) & ((0x9d2c << 16) | 0x5680);
    $y ^= ($y << 15) & (0xefc6 << 16);
    $y ^= ($y >> 18) & $this->MASK14;

    return $y;
  }

  function signed2unsigned($signed_integer) {
	  ## assert(is_integer($signed_integer));
	  ## assert(($signed_integer & ~$this->MASK32) === 0);

	  return $signed_integer >= 0? $signed_integer:
		  $this->TWO_TO_THE_32 + $signed_integer;
  }

  function unsigned2signed($unsigned_integer) {
	  ## assert($unsigned_integer >= 0);
	  ## assert($unsigned_integer < pow(2, 32));
	  ## assert(floor($unsigned_integer) === floatval($unsigned_integer));

	  return intval($unsigned_integer < $this->TWO_TO_THE_31? $unsigned_integer:
		  $unsigned_integer - $this->TWO_TO_THE_32);
  }

  function force_32_bit_int($x) {
  /*
	 it would be un-PHP-like to require is_integer($x),
	 so we have to handle cases like this:

		$x === pow(2, 31)
		$x === strval(pow(2, 31))

	 we are also opting to do something sensible (rather than dying)
	 if the seed is outside the range of a 32-bit unsigned integer.
	*/

	  if(is_integer($x)) {
	 /* 
		we mask in case we are on a 64-bit machine and at least one
		bit is set between position 32 and position 63.
	  */
		  return $x & $this->MASK32;
	  } else {
		  $x = floatval($x);

		  $x = $x < 0? ceil($x): floor($x);

		  $x = fmod($x, $this->TWO_TO_THE_32);

		  if($x < 0)
			  $x += $this->TWO_TO_THE_32;

		  return $this->unsigned2signed($x);
	  }
  }

  /*
	takes 2 integers, treats them as unsigned 32-bit integers,
	and adds them.

	it works by splitting each integer into
	2 "half-integers", then adding the high and low half-integers
	separately.

	a slight complication is that the sum of the low half-integers
	may not fit into 16 bits; any "overspill" is added to the sum
	of the high half-integers.
 */ 
  function add_2($n1, $n2) {
	  $x = ($n1 & 0xffff) + ($n2 & 0xffff);

	  return 
		  (((($n1 >> 16) & 0xffff) + 
		  (($n2 >> 16) & 0xffff) + 
		  ($x >> 16)) << 16) | ($x & 0xffff);
  }

  function mul($a, $b) {
  /*
	 a and b, considered as unsigned integers, can be expressed as follows:

		a = 2**16 * a1 + a2,

		b = 2**16 * b1 + b2,

		where

	0 <= a2 < 2**16,
	0 <= b2 < 2**16.

	 given those 2 equations, what this function essentially does is to
	 use the following identity:

		a * b = 2**32 * a1 * b1 + 2**16 * a1 * b2 + 2**16 * b1 * a2 + a2 * b2

	 note that the first term, i.e. 2**32 * a1 * b1, is unnecessary here,
	 so we don't compute it.

	 we could make the following code clearer by using intermediate
	 variables, but that would probably hurt performance.
	*/

	  return
		  $this->unsigned2signed(
			  fmod(
				  $this->TWO_TO_THE_16 *
	  /*
		 the next line of code calculates a1 * b2,
		 the line after that calculates b1 * a2, 
		 and the line after that calculates a2 * b2.
		*/
				  ((($a >> 16) & 0xffff) * ($b & 0xffff) +
				  (($b >> 16) & 0xffff) * ($a & 0xffff)) +
				  ($a & 0xffff) * ($b & 0xffff),

				  $this->TWO_TO_THE_32));
  }

  function rand($low,$high){
	  $pick=floor($low+($high-$low+1)*$this->real_halfopen());
	  return ($pick);
  }

  function array_rand($array){
	  return($this->rand(0,count($array)-1));
	}
}