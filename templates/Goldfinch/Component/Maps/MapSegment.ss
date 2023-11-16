$MapElement

<div class="container">
  <div class="row justify-content-center my-5">
    <div class="col-md-8">
      <h2>Map segment</h2>
      <hr>
      <p>To overwrite this template, create individual file in your theme</p>
      <div class="mb-3"><code>/templates/Components/Maps/{$Type}.ss</code></div>
      <div class="mb-2"><strong>ID:</strong> $ID</div>
      <div class="mb-2"><strong>Type:</strong> $Type</div>
      <%-- <div class="mb-2"><strong>Parameters (json):</strong> $Parameters</div> --%>
      <% if $getSegmentTypeConfig('settings') %>
        <div class="mb-2"><strong>(config param) Settings:</strong> true</div>
      <% end_if %>
      <% if $getSegmentTypeConfig('points') %>
        <div class="mb-2"><strong>(config param) Points:</strong> true</div>
      <% end_if %>
      <% loop $ViewJson($Parameters) %>
        <%-- ... --%>
      <% end_loop %>
    </div>
  </div>
</div>
