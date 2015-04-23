# Introduction #

The `<break />` tag defines a printable page break.


# Details #

Every Nerdcules character sheet can have any number of `<break />` tags. A `<break />` tag must appear inside of the [&lt;sheet&gt;](TagSheet.md) tag, and the first `<break />` tag cannot appear before the first [&lt;set&gt;](TagSet.md) tag. This should only be used as a complete tag.

It is highly recommended that Nerdcules character sheets adhere to the layout of the original sheet design as much as possible. The `<break />` tag allows you to force a page break after a certain [&lt;set&gt;](TagSet.md) in order to imitate the original sheet. This page break is only apparent when printing, and layout conformity should be ensured using the browser's "Print Preview" function.

## Example ##

```
<sheet>
  <head>
  </head>
  <set>
  </set>
  <break />
  <set>
  </set>
</sheet>
```