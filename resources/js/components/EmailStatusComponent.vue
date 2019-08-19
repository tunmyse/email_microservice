<template>
  <div class="container">
    <div class="row mb-5">
      <h2>Email Status</h2>
    </div>
    <div class="row">
      <dl class="row">
        <dt class="col-sm-3">Subject</dt>
        <dd class="col-sm-9">{{ email.subject }}</dd>

        <dt class="col-sm-3">Body</dt>
        <dd class="col-sm-9">
          <p>{{ email.body }}</p>
        </dd>

        <dt class="col-sm-3">Format</dt>
        <dd class="col-sm-9">{{ email.format }}</dd>
      </dl>
    </div>

    <div class="row">        
      <h3 class="mb-2">Recipeints</h3>
      <table class="table table-striped table-hover">
        <thead class="thead-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Email</th>
            <th scope="col">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(recipient, index) in recipients">
            <th scope="row">{{ index + 1}}</th>
            <td>{{ recipient.email }}</td>
            <td>{{ recipient.status }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    console.log("Component mounted.");
    axios.get('/api/recipient/'+this.email.id).then((res)=>{
          this.recipients = res.data.data;
      });
  },
  data() {
    return {
      recipients: []
    };
  },
  props: ["email"]
};
</script>
