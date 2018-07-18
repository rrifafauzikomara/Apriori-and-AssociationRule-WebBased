<?
/**
 * Title: Print a HTML Table to PDF file
 * Class: PDFTable
 * Author: vietcom (vncommando at yahoo dot com)
 * Version: 1.3
 *
 * Update to 1.1 (2-Nov-2004):
 *		- Support long table in many page. Repeatable for some rows on each page.
 *		  Limitation: The height of each row can't great than the page height-the height of repeat title
 * Update to 1.2 (18 Nov 2004):
 *		- Fix some errors
 * Edited: 25 Feb 2005
 *		- In function _tableWriteRow(): correct $this-> after each row
 * Edited: 24 Apr 2005
 *		- Replace form 'foreach($array as $value)' to form 'foreach($array as $index=>$value)'
 * Update to 1.3: 19 Dec 2006
 * 		Thanks to Vincent Tomi (a czech), he improved my class from May-2005
 * 		- Support many tables (but not inside other table)
 * 		- Support font/size/style for each cell
 * 		- Border width can be changed
 * Update to 1.4: 26 March 2007
 * 		- Fix error cellwidth is not exactly when use font/size/style
 * 		- Increase height of line 1 user unit
 */
require_once('fpdf.inc.php');
require_once('color.inc.php');
require_once('htmlparser.inc.php');

class PDFTable extends FPDF{
var $left;			//Toa do le trai cua trang
var $right;			//Toa do le phai cua trang
var $top;			//Toa do le tren cua trang
var $bottom;		//Toa do le duoi cua trang
var $width;			//Width of writable zone of page
var $height;		//Height of writable zone of page
var $defaultFontFamily ;
var $defaultFontStyle;
var $defaultFontSize;

function PDFTable($orientation='P',$unit='mm',$format=array(400,455)){
	parent::FPDF($orientation,$unit,$format);
	$this->SetMargins(0,0,0,0);
	$this->SetAuthor('Pham Minh Dung');
	$this->_makePageSize();
}

function SetMargins($left,$top,$right=-1){
	parent::SetMargins($left, $top, $right);

	$this->_makePageSize();
}

function SetLeftMargin($margin){
	parent::SetLeftMargin($margin);
	$this->_makePageSize();
}

function SetRightMargin($margin){
	parent::SetRightMargin($margin);
	$this->_makePageSize();
}

function Header(){
	parent::Header(); 
	$this->SetY(($this->h*-1));
	//$this->image('images/logo.png',$this->lMargin,2,70,10);
    $this->SetY(($this->h*-1));
    $this->SetFont('Arial','B',10);
  	$this->SetTextColor(0);
	//$this->Cell(0,10,'Sistem Informasi Perkiraan ',0,1,'R');
    $this->SetY(($this->h*-1)+5);
	//$this->Cell(0,10,'CV. Surabaya Intelectual Club ',0,1,'R');

	//$this->SetFillColor(0,0,0);
	//$this->Cell($this->w-$this->rMargin-$this->lMargin,0.2,'',1,0,'L',1);

	//Return Font to normal
    //$this->SetFont('Arial','',11);
	//$this->SetX($this->lMargin);
	//$this->SetY(($this->h*-1)+$this->tMargin);

	$this->_makePageSize();
}
function _makePageSize(){
	if ($this->CurOrientation=='P'){
		$this->left		= $this->lMargin;
		$this->right	= $this->fw - $this->rMargin;
		$this->top		= $this->tMargin;
		$this->bottom	= $this->fh - $this->bMargin;
		$this->width	= $this->right - $this->left;
		$this->height	= $this->bottom - $this->tMargin;
	}else{
		$this->left		= $this->tMargin;
		$this->right	= $this->fh - $this->bMargin;
		$this->top		= $this->rMargin;
		$this->bottom	= $this->fw - $this->rMargin;
		$this->width	= $this->right - $this->left;
		$this->height	= $this->bottom - $this->lMargin;
	}
}

function Footer()
{
//! @return void
//! @desc The footer is printed in every page!
    //Position at 1.0 cm from bottom
	parent::Footer(); 
	$this->SetY(-10);
    //Copyright //especial para esta versão
    $this->SetFont('Arial','B',9);
  	$this->SetTextColor(0);
    //Arial italic 9
    $this->SetFont('Arial','I',9);
    //Page number
	//$this->SetFillColor(0,0,0);
	//$this->Cell($this->w-$this->rMargin-$this->lMargin,0.2,'',1,0,'L',1);
    //$this->Cell(0,10,$this->PageNo(),0,0,'R');
    //Return Font to normal
    //$this->SetFont('Arial','',11);
}

/**
 * @return int
 * Tra ve chieu cao cua 1 dong theo font hien hanh
 */
function getLineHeight($fontSize = 0){
	if ($fontSize == 0) $fontSize = $this->FontSizePt;
	return ($fontSize/$this->k)+1;
}

/**
 * @return int
 * @desc Tinh so dong cua $txt khi hien thi trong cell co width la $w
 */
function countLine($w,$txt){
    //Computes the number of lines a MultiCell of width w will take
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 and $s[$nb-1]=="\n")
        $nb--;
    $sep=-1;
    $i=$j=$l=0;
    $nl=1;
    while($i<$nb){
        $c=$s[$i];
        if($c=="\n"){
            $i++;
            $sep=-1;
            $j=$i;
            $l=0;
            $nl++;
            continue;
        }
        if($c==' ')
            $sep=$i;
        $l+=$cw[$c];
        if($l>$wmax){
            if($sep==-1){
                if($i==$j)
                    $i++;
            }
            else
                $i=$sep+1;
            $sep=-1;
            $j=$i;
            $l=0;
            $nl++;
        }
        else
            $i++;
    }
    return $nl;
}

/**
 * @return array
 * @desc Parse a string in html and return array of attribute of table
 */
function _tableParser(&$html){
	$align = array('left'=>'L','center'=>'C','right'=>'R','top'=>'T','middle'=>'M','bottom'=>'B');
	$t = new TreeHTML(new HTMLParser($html), 0);
	$row	= $col	= -1;
	$table['nc'] = $table['nr'] = 0;
	$table['repeat'] = array();
	$cell   = array();
	foreach ($t->name as $i=>$element){
		if ($t->type[$i] != NODE_TYPE_ELEMENT && $t->type[$i] != NODE_TYPE_TEXT) continue;
		switch ($element){
			case 'table':
				$a	= &$t->attribute[$i];
				if (isset($a['width']))		$table['w']	= intval($a['width']);
				if (isset($a['height']))	$table['h']	= intval($a['height']);
				if (isset($a['align']))		$table['a']	= $align[strtolower($a['align'])];
				$table['border'] = (isset($a['border']))?	$a['border']: 0;
				if (isset($a['bgcolor']))	$table['bgcolor'][-1]	= $a['bgcolor'];
				break;
			case 'tr':
				$row++;
				$table['nr']++;
				$col = -1;
				$a	= &$t->attribute[$i];
				if (isset($a['bgcolor']))	$table['bgcolor'][$row]	= $a['bgcolor'];
				if (isset($a['repeat']))	$table['repeat'][]		= $row;
				break;
			case 'td':
				$col++;while (isset($cell[$row][$col])) $col++;
				//Update number column
				if ($table['nc'] < $col+1)		$table['nc']		= $col+1;
				$cell[$row][$col] = array();
				$c = &$cell[$row][$col];
				$a	= &$t->attribute[$i];
				$c['text'] = array();
				$c['s']	= 2;
				if (isset($a['width']))		$c['w']		= intval($a['width']);
				if (isset($a['height']))	$c['h']		= intval($a['height']);
				if (isset($a['align']))		$c['a']		= $align[strtolower($a['align'])];
				if (isset($a['valign']))	$c['va']	= $align[strtolower($a['valign'])];
				if (isset($a['border']))	$c['border']	= $a['border'];
					else					$c['border']	= $table['border'];
				if (isset($a['bgcolor']))	$c['bgcolor']	= $a['bgcolor'];
				$cs = $rs = 1;
				if (isset($a['colspan']) && $a['colspan']>1)	$cs = $c['colspan']	= intval($a['colspan']);
				if (isset($a['rowspan']) && $a['rowspan']>1)	$rs = $c['rowspan']	= intval($a['rowspan']);
				if (isset($a['size']))		$c['fontSize']   	= $a['size'];
				if (isset($a['family']))	$c['fontFamily'] 	= $a['family'];
				if (isset($a['style']))		$c['fontStyle']  	= $a['style'];
				if (isset($a['color']))		$c['color'] 		= $a['color'];
				//Chiem dung vi tri de danh cho cell span
				for ($k=$row;$k<$row+$rs;$k++) for($l=$col;$l<$col+$cs;$l++){
					if ($k-$row || $l-$col)
						$cell[$k][$l] = 0;
				}
				if (isset($a['nowrap']))	$c['nowrap']= 1;
				break;
			case 'Text':
				$this->_setTextAndSize($c,$this->_html2text($t->value[$i]));
				break;
			case 'img':
				$a	= &$t->attribute[$i];
				if (isset($a['src'])){
					$this->_setImage($c,$a);
				}
				break;
			case 'br':
				$c = &$cell[$row][$col];
				$cn = isset($c['font'])?count($c['font'])-1:0;
				$c['font'][$cn]['line'][] = 'br';
				break;
		}
	}
	$table['cells'] = $cell;
	$table['wc']	= array_pad(array(),$table['nc'],array('miw'=>0,'maw'=>0));
	$table['hr']	= array_pad(array(),$table['nr'],0);
	return $table;
}

function _setTextAndSize(&$c, $text){
	$c['text'][] = $text;

	$this->_setFontText($c);
	$width =  $this->GetStringWidth($text)+3;
	if (!isset($c['s']) || $c['s'] < $width) $c['s'] = $width;
}

private function _setImage(&$c, &$a){
	$path = $a['src'];
	if (!is_file($path)){
		$this->Error('Image is not exists: '.$path);
	}else{
		list($u,$d) = $this->_getResolution($path);
		$c['img'] = $path;
		list($c['w'],$c['h'],) = getimagesize($path);
		if (isset($a['width'])) $c['w'] = $a['width'];
		if (isset($a['height'])) $c['h'] = $a['height'];
		$scale = 1;
		if ($u==1) $scale = 25.4/$d;
		elseif ($u==2) $scale = 10/$d;
		$c['w'] = intval($c['w']*$scale);
		$c['h'] = intval($c['h']*$scale);
	}
}
private function _getResolution($path)
{
	$pos=strrpos($path,'.');
	if(!$pos)
		$this->Error('Image file has no extension and no type was specified: '.$path);
	$type=substr($path,$pos+1);
	$type=strtolower($type);
	if($type=='jpeg')
		$type='jpg';
	if ($type!='jpg')
		$this->Error('Unsupported image type: '.$path);
	$f = fopen($path,'r');
	fseek($f,13,SEEK_SET);
	$info = fread($f,3);
    fclose($f);
    $iUnit = ord($info{0});
    $iX = ord($info{1}) * 256 + ord($info{2});
    return array($iUnit, $iX);
}

function _html2text($text){
	$text = str_replace('&nbsp;',' ',$text);
	$text = str_replace('&lt;','<',$text);
	return $text;
}

//table		Array of (w, h, bc, nr, wc, hr, cells)
//w			Width of table
//h			Height of table
//bc		Number column
//nr		Number row
//hr		List of height of each row
//wc		List of width of each column
//cells		List of cells of each rows, cells[i][j] is a cell in table
function _tableColumnWidth(&$table){
	$cs = &$table['cells'];
	$mw = $this->getStringWidth('W');
	$nc = $table['nc'];
	$nr = $table['nr'];
	$listspan = array();
	//Xac dinh do rong cua cac cell va cac cot tuong ung
	for ($j=0;$j<$nc;$j++){
		$wc = &$table['wc'][$j];
		for ($i=0;$i<$nr;$i++){
			if (isset($cs[$i][$j]) && $cs[$i][$j]){
				$c   = &$cs[$i][$j];
				$miw = $mw;
				$c['maw']	= $c['s'];
				if (isset($c['nowrap']))			$miw = $c['maw'];
				if (isset($c['w'])){
					if ($miw<$c['w'])	$c['miw'] = $c['w'];
					if ($miw>$c['w'])	$c['miw'] = $c['w']	  = $miw;
					if (!isset($wc['w'])) $wc['w'] = 1;
				}else{
					$c['miw'] = $miw;
				}
				if ($c['maw']  < $c['miw'])			$c['maw']  = $c['miw'];
				if (!isset($c['colspan'])){
					if ($wc['miw'] < $c['miw'])		$wc['miw']	= $c['miw'];
					if ($wc['maw'] < $c['maw'])		$wc['maw']	= $c['maw'];
				}else $listspan[] = array($i,$j);
			}
		}
	}
	//Xac dinh su anh huong cua cac cell colspan len cac cot va nguoc lai
	$wc = &$table['wc'];
	foreach ($listspan as $_z=>$span){
		list($i,$j) = $span;
		$c = &$cs[$i][$j];
		$lc = $j + $c['colspan'];
		if ($lc > $nc) $lc = $nc;

		$wis = $wisa = 0;
		$was = $wasa = 0;
		$list = array();
		for($k=$j;$k<$lc;$k++){
			$wis += $wc[$k]['miw'];
			$was += $wc[$k]['maw'];
			if (!isset($c['w'])){
				$list[] = $k;
				$wisa += $wc[$k]['miw'];
				$wasa += $wc[$k]['maw'];
			}
		}
		if ($c['miw'] > $wis){
			if (!$wis){//Cac cot chua co kich thuoc => chia deu
				for($k=$j;$k<$lc;$k++) $wc[$k]['miw'] = $c['miw']/$c['colspan'];
			}elseif (!count($list)){//Khong co cot nao co kich thuoc auto => chia deu phan du cho tat ca
				$wi = $c['miw'] - $wis;
				for($k=$j;$k<$lc;$k++)
					$wc[$k]['miw'] += ($wc[$k]['miw']/$wis)*$wi;
			}else{//Co mot so cot co kich thuoc auto => chia deu phan du cho cac cot auto
				$wi = $c['miw'] - $wis;
				foreach ($list as $_z2=>$k)
					$wc[$k]['miw'] += ($wc[$k]['miw']/$wisa)*$wi;
			}
		}
		if ($c['maw'] > $was){
			if (!$wis){//Cac cot chua co kich thuoc => chia deu
				for($k=$j;$k<$lc;$k++) $wc[$k]['maw'] = $c['maw']/$c['colspan'];
			}elseif (!count($list)){//Khong co cot nao co kich thuoc auto => chia deu phan du cho tat ca
				$wi = $c['maw'] - $was;
				for($k=$j;$k<$lc;$k++)
					$wc[$k]['maw'] += ($wc[$k]['maw']/$was)*$wi;
			}else{//Co mot so cot co kich thuoc auto => chia deu phan du cho cac cot auto
				$wi = $c['maw'] - $was;
				foreach ($list as $_z2=>$k)
					$wc[$k]['maw'] += ($wc[$k]['maw']/$wasa)*$wi;
			}
		}
	}
}

/**
 * @desc Xac dinh chieu rong cua table
 */
function _tableWidth(&$table){
	$wc = &$table['wc'];
	$nc = $table['nc'];
	$a = 0;
	for ($i=0;$i<$nc;$i++){
		$a += isset($wc[$i]['w']) ? $wc[$i]['miw'] : $wc[$i]['maw'];
	}
	if ($a > $this->width) $table['w'] = $this->width;

	if (isset($table['w'])){
		$wis = $wisa = 0;
		$list = array();
		for ($i=0;$i<$nc;$i++){
			$wis += $wc[$i]['miw'];
			if (!isset($wc[$i]['w'])){ $list[] = $i;$wisa += $wc[$i]['miw'];}
		}
		if ($table['w'] > $wis){
			if (!count($list)){//Khong co cot nao co kich thuoc auto => chia deu phan du cho tat ca
				//$wi = $table['w'] - $wis;
				$wi = ($table['w'] - $wis)/$nc;
				for($k=0;$k<$nc;$k++)
					//$wc[$k]['miw'] += ($wc[$k]['miw']/$wis)*$wi;
					$wc[$k]['miw'] += $wi;
			}else{//Co mot so cot co kich thuoc auto => chia deu phan du cho cac cot auto
				//$wi = $table['w'] - $wis;
				$wi = ($table['w'] - $wis)/count($list);
				foreach ($list as $_z2=>$k)
					//$wc[$k]['miw'] += ($wc[$k]['miw']/$wisa)*$wi;
					$wc[$k]['miw'] += $wi;
			}
		}
		for ($i=0;$i<$nc;$i++){
			$a = $wc[$i]['miw'];
			unset($wc[$i]);
			$wc[$i] = $a;
		}
	}else{
		$table['w'] = $a;
		for ($i=0;$i<$nc;$i++){
			$a = isset($wc[$i]['w']) ? $wc[$i]['miw'] : $wc[$i]['maw'];
			unset($wc[$i]);
			$wc[$i] = $a;
		}
	}
	$table['w'] = array_sum($wc);
}

function _tableHeight(&$table){
	$cs = &$table['cells'];
	$nc = $table['nc'];
	$nr = $table['nr'];
	$listspan = array();
	for ($i=0;$i<$nr;$i++){
		$hr = &$table['hr'][$i];
		for ($j=0;$j<$nc;$j++){
			if (isset($cs[$i][$j]) && $cs[$i][$j]){
				$c = &$cs[$i][$j];
				list($x,$cw) = $this->_tableGetWCell($table, $i,$j);

				$fontSize = (isset($c['fontSize']) && ($c['fontSize'] >0))? $c['fontSize']: 0;

				$ch = $this->countLine($cw, implode("\n", $c['text'])) * $this->getLineHeight($fontSize);
				$c['ch'] = $ch;

				if (isset($c['h']) && $c['h'] > $ch) $ch = $c['h'];

				if (isset($c['rowspan'])) $listspan[] = array($i,$j);
				elseif ($hr < $ch)				$hr         = $ch;
				$c['mih'] = $ch;
			}
		}
	}
	$hr = &$table['hr'];
	foreach ($listspan as $_z=>$span){
		list($i,$j) = $span;
		$c = &$cs[$i][$j];
		$lr = $i + $c['rowspan'];
		if ($lr > $nr) $lr = $nr;
		$hs = $hsa = 0;
		$list = array();
		for($k=$i;$k<$lr;$k++){
			$hs += $hr[$k];
			if (!isset($c['h'])){
				$list[] = $k;
				$hsa += $hr[$k];
			}
		}
		if ($c['mih'] > $hs){
			if (!$hs){//Cac dong chua co kich thuoc => chia deu
				for($k=$i;$k<$lr;$k++) $hr[$k] = $c['mih']/$c['rowspan'];
			}elseif (!count($list)){//Khong co dong nao co kich thuoc auto => chia deu phan du cho tat ca
				$hi = $c['mih'] - $hs;
				for($k=$i;$k<$lr;$k++)
					$hr[$k] += ($hr[$k]/$hs)*$hi;
			}else{//Co mot so dong co kich thuoc auto => chia deu phan du cho cac dong auto
				$hi = $c['mih'] - $hsa;
				foreach ($list as $_z2=>$k)
					$hr[$k] += ($hr[$k]/$hsa)*$hi;
			}
		}
	}
	$table['repeatH'] = 0;
	if (count($table['repeat'])){
		foreach ($table['repeat'] as $_z=>$i) $table['repeatH'] += $hr[$i];
	}else $table['repeat'] = 0;
}

/**
 * @desc Xac dinh toa do va do rong cua mot cell
 */
function _tableGetWCell(&$table, $i,$j){
	$c = &$table['cells'][$i][$j];
	if ($c){
		if (isset($c['x0'])) return array($c['x0'], $c['w0']);
		$x = 0;
		$wc = &$table['wc'];
		for ($k=0;$k<$j;$k++) $x += $wc[$k];
		$w = $wc[$j];
		if (isset($c['colspan'])){
			for ($k=$j+$c['colspan']-1;$k>$j;$k--)
				$w += $wc[$k];
		}
		$c['x0'] = $x;
		$c['w0'] = $w;
		return array($x, $w);
	}
	return array(0,0);
}

function _tableGetHCell(&$table, $i,$j){
	$c = &$table['cells'][$i][$j];
	if ($c){
		if (isset($c['h0'])) return $c['h0'];
		$hr = &$table['hr'];
		$h = $hr[$i];
		if (isset($c['rowspan'])){
			for ($k=$i+$c['rowspan']-1;$k>$i;$k--)
				$h += $hr[$k];
		}
		$c['h0'] = $h;
		return $h;
	}
	return 0;
}

function _tableRect($x, $y, $w, $h, $type=1){
	if (strlen($type)==4)
	{
		$x2 = $x + $w; $y2 = $y + $h;
		if (intval($type{0})) $this->Line($x , $y , $x2, $y );
		if (intval($type{1})) $this->Line($x2, $y , $x2, $y2);
		if (intval($type{2})) $this->Line($x , $y2, $x2, $y2);
		if (intval($type{3})) $this->Line($x , $y , $x , $y2);
	}
	elseif ((int)$type===1)
		$this->Rect($x, $y, $w, $h);
	elseif ((int)$type>1 && (int)$type<11)
	{
		$width = $this->LineWidth;
		$this->SetLineWidth($type * $this->LineWidth);
		$this->Rect($x, $y, $w, $h);
		$this->SetLineWidth($width);
	}
}

function _tableDrawBorder(&$table){
	//When fill a cell, it overwrite the border of prevous cell, then I have to draw border at the end
	foreach ($table['listborder'] as $_z=>$c){
		list($x,$y,$w,$h,$s) = $c;
		$this->_tableRect($x,$y,$w,$h,$s);
	}

	$table['listborder'] = array();
}
function _tableWriteRow(&$table,$i,$x0){
	$maxh = 0;
	for ($j=0;$j<$table['nc'];$j++){
		$h = $this->_tableGetHCell($table, $i, $j);
		if ($maxh < $h) $maxh = $h;
	}
	if ($table['lasty']+$maxh>$this->bottom && $table['multipage']){
		if ($maxh+$table['repeatH'] > $this->height){
			$msg = 'Height of this row is greater than page height!';
			$h = $this->countLine(0,$msg) * $this->getLineHeight();
			$this->SetFillColor(255,0,0);
			$this->Rect($this->x, $this->y=$table['lasty'], $table['w'], $h, 'F');
			$this->MultiCell($table['w'],$this->getLineHeight(),$msg);
			$table['lasty'] += $h;
			return ;
		}
		$this->_tableDrawBorder($table);
		$this->AddPage($this->CurOrientation);

		$table['lasty'] = $this->y;
		if ($table['repeat']){
			foreach ($table['repeat'] as $_z=>$r){
				if ($r==$i) continue;
				$this->_tableWriteRow($table,$r,$x0);
			}
		}
	}
	$y = $table['lasty'];
	for ($j=0;$j<$table['nc'];$j++){
		if (isset($table['cells'][$i][$j]) && $table['cells'][$i][$j]){
			$c = &$table['cells'][$i][$j];
			list($x,$w) = $this->_tableGetWCell($table, $i, $j);
			$h = $this->_tableGetHCell($table, $i, $j);
			$x += $x0;
			//Align
			$this->x = $x; $this->y = $y;
			$align = isset($c['a'])? $c['a'] : 'L';
			//Vertical align
			$verticalAlign = (!isset($c['va']))? 'T': $c['va'];
			$verticalAlign = (strpos('TMB', $verticalAlign)=== false)? 'T': $verticalAlign;

			if ($verticalAlign == 'M')	   $this->y += ($c['mih']>$c['ch'])? ($h-$c['ch'])/2: ($h-$c['mih'])/2;
			elseif ($verticalAlign == 'B') $this->y += ($c['mih']>$c['ch'])? $h-$c['ch']: $h-$c['mih'];
			//Fill
			$fill = isset($c['bgcolor']) ? $c['bgcolor']
				: (isset($table['bgcolor'][$i]) ? $table['bgcolor'][$i]
				: (isset($table['bgcolor'][-1]) ? $table['bgcolor'][-1] : 0));
			if ($fill){
				$color = Color::HEX2RGB($fill);
				$this->SetFillColor($color[0],$color[1],$color[2]);
				$this->Rect($x, $y, $w, $h, 'F');
			};
			//Content
			$f = $this->_setFontText($c);

			if (isset($c['color']))
			{
				$color = Color::HEX2RGB($c['color']);
				$this->SetTextColor($color[0],$color[1],$color[2]);
			}else unset($color);

			$this->MultiCell($w,$this->getLineHeight($f),implode("\n",$c['text']),0,$align);

			if (isset($color))
				$this->SetTextColor(0);

			//Border
			if (isset($c['border'])){
				$table['listborder'][] = array($x,$y,$w,$h,$c['border']);
			}elseif (isset($table['border']) && $table['border'])
				$table['listborder'][] = array($x,$y,$w,$h,$table['border']);
		}
	}
	$table['lasty'] += $table['hr'][$i];
	$this->y = $table['lasty'];
}
function _setFontText(&$c){
	//$count = 0;

	if (isset($c['fontSize']) && ($c['fontSize'] >0)){
		$fontSize   = $c['fontSize'];
		//$count++;
	}else $fontSize   = $this->defaultFontSize;
	if (isset($c['fontFamily'])){
		$fontFamily = $c['fontFamily'];
		//$count++;
	}else $fontFamily = $this->defaultFontFamily;
	if (isset($c['fontStyle'])){
          $STYLE     = explode(",", $c['fontStyle']);
          $fontStyle = '';

          foreach($STYLE AS $si=>$style)  $fontStyle .= strtoupper(substr(trim($style), 0, 1));
         // $count++;
	}else $fontStyle  = $this->defaultFontStyle;

   $this->SetFont($fontFamily, $fontStyle, $fontSize);
   return $fontSize;
}
function _tableWrite(&$table){
	//if ($table['w']>$this->width+5)
	//debug($this->CurOrientation,$table['w'],$this->width);
	if ($this->CurOrientation == 'P' && $table['w']>$this->width+5) $this->AddPage('L');
	$x0 = $this->x;
	$y0 = $this->y;
	if (isset($table['a'])){
		if ($table['a']=='C'){
			$x0 += (($this->right-$x0) - $table['w'])/2;
		}elseif ($table['a']=='R'){
			$x0 = $this->right - $table['w'];
		}
	}
	$table['lasty'] = $y0;
	$table['listborder'] = array();
	for ($i=0;$i<$table['nr'];$i++) $this->_tableWriteRow($table, $i, $x0);
	$this->_tableDrawBorder($table);
	//echo "<pre>";print_r($table);
}

function htmltable(&$html,$multipage=1){
	$a = $this->AutoPageBreak;
	$this->SetAutoPageBreak(0,$this->bMargin);
	$HTML = explode("<table", $html);
	foreach ($HTML as $i=>$table)
	{
		if (strlen($table)<6) continue;
		$table = '<table' . $table;
		$table = $this->_tableParser($table);
		$table['multipage'] = $multipage;
		$this->_tableColumnWidth($table);
		$this->_tableWidth($table);
		$this->_tableHeight($table);
		$this->_tableWrite($table);
	}
	$this->SetAutoPageBreak($a,$this->bMargin);
}
}//PDFTable
?>