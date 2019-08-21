<template>
  <div class="container">
    <div class="row">
      <table class="table table-striped table-hover">
        <thead class="thead-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Subject</th>
            <th scope="col">Body</th>
            <th scope="col">Format</th>
            <th scope="col">View Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(email, index) in emails">
            <th scope="row">{{ index + 1}}</th>
            <td>{{ email.subject }}</td>
            <td class="text-truncate">{{ email.body }}</td>
            <td>{{ email.format }}</td>
            <td>
              <button class="btn btn-secondary" v-on:click="viewStatus(index)">View Details</button>
            </td>
          </tr>
          <tr v-if="emails.length == 0">
            <td colspan="5">
              You have not sent any email using this microservice!
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    console.log("List component mounted.");
    axios.get("/api/email").then(res => {
      this.emails = res.data.data;
    });
  },

  data() {
    return {
      emails: [],
      error: null,
      loading: false
    };
  },

  methods: {
    viewStatus: function(index) {
      console.log(this.emails);
      this.$router.push({
        name: "status",
        params: {
          email: this.emails[index]
        }
      });
    }
  }
};
</script>
