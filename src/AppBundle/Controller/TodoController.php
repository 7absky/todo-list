<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Todo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TodoController extends Controller
{
    /**
     * @Route("/todo", name="todo_list")
     */
    public function listAction()
    {   
        $todos = $this->getDoctrine()
            ->getRepository('AppBundle:Todo')
            ->findAll();
        
        // replace this example code with whatever you need
        return $this->render('todo/index.html.twig', array (
                'todos' => $todos
            ));
    }

    /**
     * @Route("/create", name="todo_create")
     */
    public function createAction(Request $request)
    {
        
        $todo = new Todo;
        $form = $this->createFormBuilder($todo)
                ->add('name', TextType::class, array ('attr' => array(
                    'class' => 'form-control', 
                    'style' => 'margin-bottom:15px'
                    )))
                ->add('category', TextType::class, array ('attr' => array(
                    'class' => 'form-control', 
                    'style' => 'margin-bottom:15px'
                    )))
                ->add('description', TextareaType::class, array ('attr' => array(
                    'class' => 'form-control', 
                    'style' => 'margin-bottom:15px'
                    )))
                ->add('priority', ChoiceType::class, array ('choices' => array('Low' => 'Low', 'Normal'=>'Normal', 'High'=>'High'), 
                    'attr' => array(
                    'class' => 'form-control', 
                    'style' => 'margin-bottom:15px'
                    )))
                ->add('due_date', DateTimeType::class, array ('attr' => array(
                    'class' => 'formcontrol', 
                    'style' => 'margin-bottom:15px'
                    )))
                ->add('save', SubmitType::class, array ('label'=>'Create Todo', 'attr' => array(
                    'class' => 'btn btn-primary', 
                    'style' => 'margin-bottom:15px'
                    )))
                ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get Data

            $name = $form['name'] ->getData();
            $category = $form['category'] ->getData();
            $description = $form['description'] ->getData();
            $priority = $form['priority'] ->getData();
            $due_date = $form['due_date'] ->getData();

            $now = new\DateTime('now');

            $todo->setName($name);
            $todo->setCategory($category);
            $todo->setDescription($description);
            $todo->setPriority($priority);
            $todo->setDueDate($due_date);
            $todo->setCreateDate($now);

            $em = $this->getDoctrine()->getManager();
            $em->persist($todo);
            $em->flush();

            $this->addFlash(
                'notice',
                'Todo Added'
            );

           
        }
        return $this->render('todo/create.html.twig', array(
            'form' => $form->createView()
        ));
    }

     /**
     * @Route("/edit/{id}", name="todo_edit")
     */
    public function editAction($id, Request $request)
    {
        // Fetch from DB
        $em = $this->getDoctrine()->getManager();
        $todo = $em->getRepository('AppBundle:Todo')->find($id);

        // Check if exists 
        if (!$id) {
            throw $this->createNotFoundException(
            'No Todo found for id '.$id
        );
        }
        // Injection of $todo values into form fields

        $form = $this->createFormBuilder($todo)
                ->add('name', TextType::class, array ('attr' => array(
                    'value' => $todo->getName(),
                    'class' => 'form-control', 
                    'style' => 'margin-bottom:15px'
                    )))
                ->add('category', TextType::class, array ('attr' => array(
                    'value' => $todo->getCategory(),
                    'class' => 'form-control', 
                    'style' => 'margin-bottom:15px'
                    )))
                ->add('description', TextareaType::class, array ('attr' => array(
                    'value' => $todo->getDescription(),
                    'class' => 'form-control', 
                    'style' => 'margin-bottom:15px'
                    )))
                ->add('priority', ChoiceType::class, array ('choices' => array('Low' => 'Low', 'Normal'=>'Normal', 'High'=>'High'), 
                    'attr' => array(
                    'value' => $todo->getPriority(),
                    'class' => 'form-control', 
                    'style' => 'margin-bottom:15px'
                    )))
                ->add('due_date', DateTimeType::class, array ('attr' => array(
                    'value' => $todo->getDueDate()->format('Y-m-d H:i:s'),
                    'class' => 'formcontrol', 
                    'style' => 'margin-bottom:15px'
                    )))
                ->add('edit', SubmitType::class, array ('label'=>'Edit Todo', 'attr' => array(
                    'class' => 'btn btn-primary', 
                    'style' => 'margin-bottom:15px'
                    )))
                ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Save edited fields 
            $now = new\DateTime('now');
            $todo->setName($form['name'] ->getData());
            $todo->setCategory($form['category'] ->getData());
            $todo->setDescription($form['description'] ->getData());
            $todo->setPriority($form['priority'] ->getData());
            $todo->setDueDate($form['due_date'] ->getData());
            $todo->setCreateDate($now);

            $em->flush();
            $this->addFlash(
                'notice',
                'Todo Edited'
            );

            return $this->redirectToRoute('todo_list');
        }
        return $this->render('todo/create.html.twig', array(
            'form' => $form->createView()
        ));
    }

     /**
     * @Route("/details/{id}", name="todo_details")
     */
    public function detailsAction($id)
    {
        $todo = $this->getDoctrine()
            ->getRepository('AppBundle:Todo')
            ->find($id);
        
        // replace this example code with whatever you need
        return $this->render('todo/details.html.twig', array (
                'todo' => $todo
            ));
    }

    /**
     * @Route("/delete/{id}", name="todo_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $todo = $em->getRepository('AppBundle:Todo')->find($id);
        $em->remove($todo);
        $em->flush();

        $this->addFlash(
                'notice',
                'Todo Removed'
            );

            return $this->redirectToRoute('todo_list');
    } 
}
