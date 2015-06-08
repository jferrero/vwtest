<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;
?>

<h1>Welcome to <i><?php echo CHtml::encode(Yii::app()->name); ?></i></h1>

<p>Comments and additional data could be found <a href="https://github.com/jferrero/vwtest/blob/master/README">here</a>,
and images with the responses could be found <a href="https://github.com/jferrero/vwtest/tree/master/vwtest/protected/data/resultimages/">here </a></p>
<br>
<?php

echo "Generic API URL could be found <a href='/index.php/vw/api'>here</a></p>";
echo "Ping URL could be found <a href='/index.php/vw/ping'>here</a></p>";
echo "Reverse API URL could be found <a href='/index.php/vw/reverse'>here</a></p>";
?>
<br><br>
Altough they will not work with GET
