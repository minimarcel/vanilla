<?php
import('vanilla.localisation.gettext.GetTextReader');
import('vanilla.util.StringMap');

class GetTextCache 
{
    private $translations;
    private $headers;
    private $pluralExpression;
    private $nbPlurals;

// -------------------------------------------------------------------------->

    public function __construct(File $file)
    {
	$this->translations = new StringMap();
	$this->headers	    = new StringMap();

	try
	{
	    $fileReader = new FileReader($file);
	    $reader	= new GetTextReader($fileReader);

	    $this->loadTranslations($reader);
	    $this->parseHeaders();
	    $this->computePluralExpression();
	}
	catch(Exception $e)
	{
	    if ( isset($fileReader) )
	    {
		$fileReader->close();
	    }

	    throw $e;
	}
    }

// -------------------------------------------------------------------------->

    private function loadTranslations(GetTextReader $reader)
    {
	$n = $reader->getNbStrings();
	for ( $i = 0 ; $i < $n ; $i++ )
	{
	    $original	    = $reader->getOriginalString($i);
	    $translation    = $reader->getTranslationString($i);

	    $this->translations->put($original, $translation);
	}
    }

    private function parseHeaders()
    {
	$headers = $this->translations->get("");

	$c = 0;
	while ( ($i = strpos($headers, "\n", $c)) !== false ) 
	{
	    $line = substr($headers, $c, $i - $c);
	    $couple = explode(":", $line);

	    $key    = trim($couple[0]);
	    $value  = trim($couple[1]);

	    $this->headers->put($key, $value);

	    $c = $i + 1;
	}
    }

    private function computePluralExpression()
    {
	$h = $this->headers->get("Plural-Forms");
	$a = explode(";", $h);

	$n = substr(trim($a[0]), strlen("nplurals="));
	$e = substr(trim($a[1]), strlen("plural="));

	$this->nbPlurals = intval($n);
	$this->pluralExpression = "\$i = " . str_replace("n", "\$n", $e) . ";";

	$this->getPluralIndexFor(2);
    }

    private function getPluralIndexFor($n)
    {
	$i;
	eval($this->pluralExpression);
	return intval($i);
    }

// -------------------------------------------------------------------------->

    public function getTranslation($original)
    {
	return $this->translations->get($original);
    }

    public function getPluralTranslation($single, $plural, $n)
    {
	$i = $this->getPluralIndexFor($n);
	$t = $this->getTranslation("$single\0$plural");
	$a = explode("\0", $t);

	return $a[$i];
    }

    public function getHeader($key)
    {
	return $this->headers->get($key);
    }
}
?>
