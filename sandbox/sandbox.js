    
<script>
function showHide(obj){
var tbody = obj.parentNode.parentNode.parentNode.getElementsByTagName("tbody")[0];
var old = tbody.style.display;
tbody.style.display = (old == "none"?"":"none");
}
</script>
<style type="text/css">
th{
text-align: left;
cursor: pointer;
}
table tbody tr td{
padding-left: 15px;
}
</style>
<table>
<tr>
<td>
<table>
<thead>
<tr>
<th onClicked="showHide(this)">menu 1</th>
</tr>
</thead>
<tbody>
<tr>
<td>sub 1</td>
</tr>
<tr>
<td>
<table>
<thead>
<tr>
<th onClicked="showHide(this)">sub 1</th>
</tr>
</thead>
<tbody>
<tr>
<td>
<table>
<thead>
<tr>
<th onClicked="showHide(this)">sub 1 1</th>
</tr>
</thead>
<tbody>
<tr>
<td>sub 1 1 1</td>
</tr>
<tr>
<td>sub 1 1 2</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td>sub 2 2</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</table>