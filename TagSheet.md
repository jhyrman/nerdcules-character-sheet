# Introduction #

The `<sheet>` tag is the root element for a Nerdcules character sheet.


# Details #

Every Nerdcules character sheet has exactly one `<sheet>` tag as the root element. The `<sheet>` must contain exactly one [&lt;head&gt;](TagHead.md), at least one [&lt;set&gt;](TagSet.md), and zero or more [&lt;break&gt;](TagBreak.md) tags.

## Example ##

```
<sheet>
  <head>
  </head>
  <set name="example">
  </set>
</sheet>
```