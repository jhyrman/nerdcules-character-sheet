# Introduction #

The `<set>` tag defines a collected set of elements (attributes, traits, etc.)


# Details #

Every Nerdcules character sheet must have at least one `<set>` tag. It must appear inside the [&lt;sheet&gt;](TagSheet.md) tag, and the first `<set>` tag must come immediately after the end of the [&lt;head&gt;](TagHead.md) tag. Each `<set>` must have at least one [&lt;column&gt;](TagColumn.md) tag. `<set>` tags cannot be nested.

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