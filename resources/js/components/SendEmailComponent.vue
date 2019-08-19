<template>
  <div class="container">
    <div :class="display">
      <div :class="alertClasses" role="alert">
        {{ message }}
      </div>
    </div>
    <div class="row mb-5">
      <h2 class="mb-5">Compose Email</h2>
    </div>
    <div class="row">
      <form>
        <div class="form-group row">
          <label for="inputSubject" class="col-sm-3 col-form-label">Subject</label>
          <div class="col-sm-9">
            <input
              type="text"
              class="form-control"
              id="inputSubject"
              v-model="emailData.subject"
              placeholder="Enter email subject"
              required
            />
          </div>
        </div>
        <div class="form-group row">
          <label for="inputBody" class="col-sm-3 col-form-label">Body</label>
          <div class="col-sm-9">
            <textarea
              class="form-control"
              id="inputBody"
              v-model="emailData.body"
              placeholder="Enter email content here..."
              required
            ></textarea>
          </div>
        </div>
        <div class="form-group row">
          <label for="inputRecipient" class="col-sm-3 col-form-label">Recipients</label>
          <div class="col-sm-9">
            <textarea
              type="text"
              class="form-control"
              id="inputRecipient"
              v-model="emailData.recipients"
              placeholder="Enter valid email addresses (Multiple email addresses should be separated by a comma) subject"
              required
            ></textarea>
          </div>
        </div>
        <fieldset class="form-group">
          <div class="row">
            <legend class="col-form-label col-sm-3 pt-0">Format</legend>
            <div class="col-sm-9">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  name="formatRadio"
                  id="plainFormat"
                  value="plain"
                  v-model="emailData.format"
                />
                <label class="form-check-label" for="plainFormat">Plain</label>
              </div>
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  name="formatRadio"
                  id="htmlFormat"
                  value="html"
                  v-model="emailData.format"
                />
                <label class="form-check-label" for="htmlFormat">HTML</label>
              </div>
              <div class="form-check disabled">
                <input
                  class="form-check-input"
                  type="radio"
                  name="formatRadio"
                  id="markdownFormat"
                  value="markdown"
                  v-model="emailData.format"
                />
                <label class="form-check-label" for="markdownFormat">Markdown</label>
              </div>
            </div>
          </div>
        </fieldset>

        <div class="form-group row">
          <div class="col-sm-10">
            <button type="button" class="btn btn-primary" v-on:click="sendEmail">Send email</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    console.log("Component mounted.");
  },

  data() {
    return {
      display: ["row", "d-none"],
      alertClasses: ["alert"],
      message: "",
      emailData: {
        subject: "",
        body: "",
        recipients: "",
        format: "plain"
      }
    };
  },

  methods: {
    sendEmail: function() {
      let reqData = {
        subject: this.emailData.subject,
        body: this.emailData.body,
        recipients: this.formatRecipients(this.emailData.recipients),
        format: this.emailData.format
      };

      let isvalid = this.validateData(reqData);

      if (!isvalid) {
        this.showErrorMessage("Please enter valid information for all the fields!");
        return;
      }

      axios
        .post("/api/sendemail", reqData)
        .then(res => {
          this.showSuccessMessage(res.data.message);
          this.resetFields();
        })
        .catch(err => {
          let message = "Unable to process your request. Please try again!";
          let resData = err.response;

          if (resData.status == 422) {
            message = "";
            for (let i = 0; i < resData.data.message.length; i++) {
              message += resData.data.message[i] + "\n";
            }
          } else if (resData.status == 500) {
            message = resData.data.message;
          }

          this.showErrorMessage(message);
          this.resetFields();
        });
    },

    resetFields: function() {
      this.emailData = {
        subject: "",
        body: "",
        recipients: "",
        format: "plain"
      };
    },

    formatRecipients: function(recipients) {
      return recipients.split(",");
    },

    validateData: function(emailData) {
      let validFormat =
        ["plain", "html", "markdown"].indexOf(emailData.format) != -1;

      if (
        emailData.subject != "" &&
        emailData.body != "" &&
        validFormat &&
        Array.isArray(emailData.recipients)
      ) {
        return true;
      }

      return false;
    },

    showMessage: function(message, type) {
      this.message = message;
      this.alertClasses[1] = "alert-" + type;
      this.display[1] = "d-block";
    },

    showSuccessMessage: function(message) {
      this.showMessage(message, "primary");
    },

    showErrorMessage: function(message) {
      this.showMessage(message, "danger");
    }
  }
};
</script>
