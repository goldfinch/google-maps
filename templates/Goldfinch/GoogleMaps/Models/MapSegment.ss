$MapElement

<%-- The below data is for your information. Feel free to remove everything and make your own changes --%>
<div class="container">
  <div class="row justify-content-center my-5">
    <div class="col-md-8">
      <hr />
      <p>This is a custom template for <strong>$Type</strong> segment type.</p>
      <div class="mb-3"><strong>Template:</strong> <code>/templates/Components/Maps/{$Type}.ss</code></div>
      <div class="mb-2"><strong>Segment ID:</strong> $ID</div>
      <div class="mb-2"><strong>Segment Type:</strong> $Type</div>
      <%--
      <div class="mb-2"><strong>Parameters (json):</strong> $Parameters</div>
      --%>
      <% if $getSegmentTypeConfig('settings') %>
        <div class="mb-2"><strong>(config param) Settings:</strong> true</div>
      <% end_if %>
      <% if $getSegmentTypeConfig('markers') %>
        <div class="mb-2"><strong>(config param) Markers:</strong> true</div>
      <% end_if %>
      <% loop $ViewJson($Parameters) %><%-- ... --%><% end_loop %>
    </div>
  </div>
</div>
