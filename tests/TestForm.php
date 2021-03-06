<?php

class TestForm extends TestCase
{
    public function testEmptyFormWithNoToken()
    {
        $expect = '<form method="GET" action="/"><input type="hidden" name="_form" value="test" /></form>';

        $this->form->open('test');
        $this->form->close()->noToken();

        $this->assertEquals($expect, $this->clean($this->form));
    }

    public function testEmptyFormWithToken()
    {
        $expect = '<form method="GET" action="/"><input type="hidden" name="_form" value="test" />'.
            '<input type="hidden" name="_token" value="secret_token" /></form>';

        $this->setToken('secret_token');

        $this->form->open('test');
        $this->form->close();

        $this->assertEquals($expect, $this->clean($this->form));
    }

    public function testElementWithLabelsAndErrors()
    {
        $label = '<label for="test-name">Name:</label>';
        $field = '<input id="test-name" name="name" type="text" value="" />';
        $error = '<strong>Error message</strong>';
        $fullForm = $this->wrap('<div>' . $label . $field . $error . '</div>');

        $this->setError('name', 'Error message');

        $this->form->open('test');
        $this->form->text('name')->label('Name:');
        $this->form->close();

        $this->assertEquals($this->form->name->label(), $label);
        $this->assertEquals($this->form->name->field(), $field);
        $this->assertEquals($this->form->name->error(), $error);
        $this->assertEquals($fullForm, $this->clean($this->form));
    }

    public function testPopulateWithModel()
    {
        $model = ['name' => 'John Doe'];

        $this->form->open('test')->model($model);
        $this->form->text('name')->label('Name:');
        $this->form->close();

        $field = '<input id="test-name" name="name" type="text" value="John Doe" />';
        $this->assertEquals($this->form->name->field(), $field);
    }

    public function testPopulateWithModelWithOffset()
    {
        $model = ['coordinates' => ['x' => '123', 'y' => '456']];

        $this->form->open('test')->model($model);
        $this->form->text('coordinates[x]')->label('X:');
        $this->form->text('coordinates[y]')->label('Y:');
        $this->form->close();

        $fieldX = '<input id="test-coordinates[x]" name="coordinates[x]" type="text" value="123" />';
        $fieldY = '<input id="test-coordinates[y]" name="coordinates[y]" type="text" value="456" />';
        $this->assertEquals($this->form['coordinates[x]']->field(), $fieldX);
        $this->assertEquals($this->form['coordinates[y]']->field(), $fieldY);
    }

    public function testPopulateWithPostValue()
    {
        $this->setPostValue('name', 'John Doe');

        $this->form->open('test');
        $this->form->text('name')->label('Name:');
        $this->form->close();

        $field = '<input id="test-name" name="name" type="text" value="John Doe" />';
        $this->assertEquals($this->form->name->field(), $field);
    }

    public function testPopulateWithOldValue()
    {
        $this->setOldValue('name', 'John Doe');

        $this->form->open('test');
        $this->form->text('name')->label('Name:');
        $this->form->close();

        $field = '<input id="test-name" name="name" type="text" value="John Doe" />';
        $this->assertEquals($this->form->name->field(), $field);
    }

    public function testRepopulateCorrectForm()
    {
        $this->setPostValue('name', 'John Doe');
        $this->setPostValue('_form', 'test1');

        $form1 = $this->createForm();
        $form1->open('test1');
        $form1->text('name');
        $form1->close();

        $form2= $this->createForm();
        $form2->open('test2');
        $form2->text('name');
        $form2->close();

        $field = '<input id="test1-name" name="name" type="text" value="John Doe" />';
        $this->assertEquals($form1->name->field(), $field);

        $field = '<input id="test2-name" name="name" type="text" value="" />';
        $this->assertEquals($form2->name->field(), $field);
    }

    public function testPopulateByPriority()
    {
        $model = ['name' => 'Jane Doe'];

        $this->form->open('test')->model($model);
        $this->form->text('name')->label('Name:')->value('John Smith');
        $this->form->close();

        $field = '<input id="test-name" name="name" type="text" value="John Smith" />';
        $this->assertEquals($this->form->name->field(), $field);

        $this->setOldValue('name', 'John Doe');

        $field = '<input id="test-name" name="name" type="text" value="John Doe" />';
        $this->assertEquals($this->form->name->field(), $field);

        $this->setPostValue('name', 'Jane Smith');

        $field = '<input id="test-name" name="name" type="text" value="Jane Smith" />';
        $this->assertEquals($this->form->name->field(), $field);
    }
}