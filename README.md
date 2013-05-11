Kohana language switcher and multilanguage ORM support
======================================================

Language switch support
-----------------------

Just add some links with href that redirects to:

```
http://yourdomain/language/change/<language>
```

<language\> supports two letters language code like "en", "fr", "pl" etc.

Then add an i18n language set method somewere in your code. Your application top controller's before() method should be a good place to put this code into.

```
i18n::lang(Session::instance()->get('_lang', Language::base()));
```

You can set the base language in your config file.

ORM multilanguage support
-------------------------

Create your database schema like this:

```
articles:
  id
  created
  updated
  author_id
  etc...

article_langs:
  id
  article_id
  language_id
  title
  content
  etc...  
```

Extend your article model with ORML:

``` php
class Model_Article extends ORML
{
  // fields and methods here
}

class Model_Article_Lang extends ORM
{
}
```

Now you can access translated records using:

```
$article = ORML::factory('article');
$article->created // access to non translated fields
$article->updated
$article->translation->title // access to translated
$article->translation->content
```

Remember to import schema and fixtures from tests/test_data/structure/test-schema-mysql.sql
