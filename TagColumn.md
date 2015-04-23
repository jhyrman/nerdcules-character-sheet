# Introduction #

The `<column>` tag defines a column of elements.


# Details #

Every Nerdcules character sheet must have at least one `<column>` tag, which must appear inside of a [&lt;set&gt;](TagSet.md) tag. Each `<column>` tag can have any number of [field](TagReference#Field_Tags.md) tags.

## Example ##

```
<sheet>
  <head>
  </head>
  <set>
    <column>
    </column>
  </set>
</sheet>
```