<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div>User List</div>
      <button class="btn btn-success" onclick="addUser()">Add User</button>
      <ul id="list">

      </ul>
    </div>
  </div>
</div>
<template id="tlist">
  <li>{name}</li>
</template>
<script>
  function addUser() {
    let tlist = document.querySelector('#tlist');
    let list = document.querySelector('#list');

    let clone = tlist.content.cloneNode(true);

    let li = clone.querySelector('li');

    li.textContent = 'RIYAN';

    erp.clone = clone;
    erp.data = li;

    list.appendChild(li);
  }
</script>