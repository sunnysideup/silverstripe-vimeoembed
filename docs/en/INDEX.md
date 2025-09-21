# tl;dr

Add the following extension to any dataobject:

```yml
MyDataObject:
  extensions:
    - Sunnysideup\Vimeoembed\Model\VimeoDOD

```

Or, for more control you can add it like this 

```php

namespace myNameSpace;

use Sunnysideup\Vimeoembed\Model\VimeoDataObject;
use SilverStripe\ORM\DataObject;

class MyDataObject extends DataObject 
{

    private static $has_one = [ // OR has_many, OR many_many, etc...
        'VimeoDataObject' => VimeoDataObject::class,
    ];

}
```
