<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <style>
      .form-viewer {
        background: #ececec;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        padding: 20px;
        border-radius: 10px;
      }
      pre.json {
        float: left;
        width: 34%;
      }
      pre.html {
        float: left;
        width: 30%;
        border-left: 1px solid #999;
        border-right: 1px solid #999;
        display: none;
      }
      .form-container {
        float: right;
        width: 34%;
        /*display: none;*/
      }
    </style>

    <h1><i class="fa fa-dashboard"></i>Form Tester</h1>
    <h4>Test form creator class. Test multiple form formats and implementations.</h4>

    
    <?php

    $forms[] = '{
  "type": "object",
  "properties": {
    "name": {
      "type": "string"
    },
    "surname": {
      "type": "string"
    }
  },
  "required": ["name", "surname"]
}';
    $forms[] = '{
  "type": "object",
  "properties": {
    "name": {
      "type": "string"
    },
    "surname": {
      "type": "string"
    }
  }
}';
    $forms[] = '{
  "type": "object",
  "properties": {
    "number":      { "type": "number" },
    "street_name": { "type": "string" },
    "street_type": { 
      "type": "string",
      "enum": ["Street", "Avenue", "Boulevard"]
    }
  }
}';

    $forms[] = '{
  "type": "object",
  "properties": {
    "username": {
      "type": "string" 
    },
    "password": { 
      "type": "string",
      "minLength": 6,
      "maxLength": 10
    }
  },
  "required": ["username", "password"]
}';

    $forms[] = '{
  "type": "object",
  "properties": {
    "name": {
      "type": "string" 
    },
    "age": { 
      "type": "number",
      "minimum": 0,
      "maximum": 200
    }
  },
  "required": ["name", "age"]
}';

    $forms[] = '{
  "type": "object",
  "properties": {
    "username": {
      "type": "string" 
    },
    "password": { 
      "type": "string",
      "minLength": 6,
      "maxLength": 10,
      "attributes" : {
        "type" : "password"
      }
    },
    "rememberMe": { 
      "type": "boolean"
    }
  },
  "required": ["username","password"]
}';

    $forms[] = '{
  "type": "array",
  "items": {
    "type": "string"
  }
}';

    $forms[] = '{
  "type": "array",
  "items": {
    "type": "number",
    "integer": true
  }
}';

    $forms[] = '{
  "type": "array",
  "items": {
    "type": "object",
    "properties": {
      "name": {
        "type": "string"
      },
      "surname": {
        "type": "string"
      }
    },
    "required": ["name", "surname"]
  }
}';

    $forms[] = '{
  "type": "object",
  "description": "A family",
  "properties": {
    "mother": {
      "type": "object",
      "properties": {
        "name": { "type": "string" },
        "surname": { "type": "string" }
      },
      "required": ["name", "surname"]
    },
    "father": {
      "type": "object",
      "properties": {
        "name": { "type": "string" },
        "surname": { "type": "string" }
      },
      "required": ["name", "surname"]
    },
    "children": {
      "type": "array",
      "items": {
        "type": "object",
        "properties": {
          "name": { "type": "string" },
          "surname": { "type": "string" }
        },
        "required": ["name", "surname"]
      }
    }
  },
  "required": ["mother", "father", "children"]
}';

    foreach($forms as $i => $form){
      echo '<div class="form-viewer clearfix">';
      echo '<pre class="json">'.$form.'</pre>';
      $processedForm = WFSimpleForm::RenderForm($form);
      echo '<pre class="html">';
      echo htmlspecialchars($processedForm['response']['data']);
      echo '</pre>';
      echo '<div class="form-container form-' . $i . '">' . $processedForm['response']['data'] . '</div>';
      //echo '<div class="form-container2 form-' . $i . '"></div>';
      echo '</div><!--/form-viewer-->';
    }
    ;?>

  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->
<script type="text/javascript">
  $(document).ready(function(){
    $("form.form-fly").each(function(a, b){
      var $this = $(this),
        jsonEncoded = atob($this.attr('data-formfly')),
        json = JSON.parse(jsonEncoded),
        key = null;

      if($this.length > 0) key = $this.attr('id').split('-')[1];
      var fff = CS_FormFly.registerForm(key, {json : json});
      console.log(jsonEncoded, json);
      return CS_FormFly.getFormByKey(key).render('.form-container.form-' + a);
    });
  });
</script>