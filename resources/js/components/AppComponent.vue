<template>
  <div>
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand" href>Takeaway</a>
        <p>
          <router-link :to="{ name: 'list' }"><button class="btn btn-secondary">Email list</button></router-link>
          <router-link :to="{ name: 'send' }"><button class="btn btn-secondary">Send Email</button></router-link>
        </p>
      </div>
    </nav>

    <main class="py-4">
      <div class="container">
        <router-view></router-view>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  mounted() {
    console.log("Component mounted.");
  }
};
</script>
