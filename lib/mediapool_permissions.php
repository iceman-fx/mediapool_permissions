<?php
/*
	Redaxo-Addon: Medienpool-Kategorien beschränken
	Basisklasse
	v0.1
	by Falko Müller @ 2025
*/

class mediapool_permissions {	

	//Media-Berechtigung prüfen
	public static function hasMediaPerm($fileId=0)
	{
		$return = false;
		$fileId	= intval($fileId);
		
		if ($fileId > 0 && is_object(rex::getUser())):
			$perm = rex::requireUser()->getComplexPerm('media');

			$sql = rex_sql::factory()->setQuery('SELECT filename FROM '.rex::getTable('media').' WHERE id = ?', [$fileId]);
			$media = ($sql->getRows() > 0) ? rex_media::get((string) $sql->getValue('filename')) : null;

			if ($media && (self::hasAllMediaPerm() || $perm->hasCategoryPerm($media->getCategoryId())) ):
				$return = true;
			endif;
		endif;
		
		return $return;
	}


	//Kategorie-Berechtigung prüfen
	public static function hasMediaCatPerm($catId=0)
	{
		$return = false;
		$catId	= intval($catId);
		
		if ($catId >= 0 && is_object(rex::getUser())):
			$perm = rex::requireUser()->getComplexPerm('media');

			if (self::hasAllMediaPerm() || $perm->hasCategoryPerm($catId)):
				$return = true;
			endif;
		endif;
		
		return $return;
	}
	

	//auf volle Berechtigung prüfen
	public static function hasAllMediaPerm()
	{
		$return = false;
		
		if (is_object(rex::getUser())):
			$perm = rex::requireUser()->getComplexPerm('media');
		
			if (rex::getUser()->isAdmin() || $perm->hasAll()):
				$return = true;
			endif;
		endif;
		
		return $return;
	}
	
}
?>